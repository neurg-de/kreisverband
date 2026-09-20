# OV, Kontakt, Rechtstexte und Termine – Implementierungsbericht

**Historischer Erstbericht:** Die unten genannten PHPCS-Fehler und Testzahlen dokumentieren den ersten Implementierungsstand, nicht den finalen Qualitätsstatus. Der Abschlussstand steht in [ov-quality-final.md](ov-quality-final.md): PHPCS ohne Fehler/Warnungen und 37 fokussierte Tests mit 218 Assertions erfolgreich. Seitdem wurden zusätzlich kanonische OV-Altseiten, tatsächliche SVG-/Mobilkartenlinks und die unabhängigen Admin-/Medien-Reviewbefunde behoben.

Worker `task_edb9390345f3`, Branch `fix/geschaeftsstelle-release`, 20.09.2026. Keine Commits, keine Änderung einer produktiven Installation.

## Änderungen

- `[ortsverband_liste]`: veröffentlichte, nicht passwortgeschützte lokale Startseite vor externer HTTP(S)-Website; keine leeren Links. `full` wird ohne Datenmigration als alter Alias für `ov` erkannt, auch beim Shortcode-Filter. `werbung` bleibt „Im Aufbau“. Die OV-Titel in `[gliederungen]` und `[arbeitsgemeinschaften]` sowie die Kreiskarte verwenden dieselbe Zielermittlung. Externe Links öffnen im bestehenden Fenster.
- Rechtstexte: nur veröffentlichte, ungeschützte Seiten; eigene OV-Auswahl vor KV-Auswahl. Ungültige KV-Ziele werden nicht verlinkt. OV-Unterseiten erben den Kontext ihrer übergeordneten Seite. Footer und Cookie-Hinweis verwenden diesen Kontext; der Cookie-Hinweis behält als letzten Rückfall die validierte WordPress-Datenschutzseite. Keine fest codierten München-Ziele eingeführt.
- OV-Adminformular: ungültige öffentliche Kontakt-E-Mail oder ungültige Rechtstext-Auswahl brechen vor Änderungen mit verständlichem Hinweis ab. Sichtbarer Hinweis, dass Kontaktadressen veröffentlicht und keine Postfächer angelegt werden. KV-E-Mail-Einstellungen werden validiert und ungültige Adressen mit Einstellungsfehler verworfen; alte ungültige Adressen werden beim Lesen nicht ausgegeben. Term-Metadaten validieren Kontakt-E-Mail und Website auch auf anderen WordPress-Schreibwegen.
- Termine: beide Shortcodes akzeptieren `zuordnung` und `kategorie`; auf OV-Seiten hat der eigene Kontext Vorrang. Die allgemeine KV-Terminübersicht ohne Filter bleibt als bisherige Gesamtübersicht erhalten. Kalender-Links übernehmen den gewählten Bereich, OV-Startseiten erhalten einen eigenen iCal-Link, der Rücklink vom OV-Termin bleibt gefiltert. Downloads einzelner Termine funktionieren auch mit einfachen Permalinks.
- Öffentliche Terminabfragen und Kalender exportieren keine Entwürfe, privaten oder passwortgeschützten Termine. Klassischer Editor und REST-Metadaten validieren Datum/Uhrzeit/HTTP(S)-Link; REST-Metadaten prüfen die Bearbeitungserlaubnis für den konkreten Termin. Alte Event-Capability-Aliase delegieren an objektbezogene WordPress-Prüfungen.
- iCal: konfigurierter WordPress-Zeitzone entsprechend UTC für Uhrzeiten; exklusives Enddatum auch für eintägige Ganztagstermine; keine ungültigen Datumswerte; Escape von Textfeldern und UTF-8-sichere Zeilenfaltung auf 75 Oktette. Bestehender Feed-Umfang bleibt maximal 200 Termine ab drei Monaten vor heute.
- Die alte automatische Zuordnungs-Migration erfasst keine Attachments mehr; bewusste Medienprüfung und Rücknahme gehören zum Rollen/Medien-Worker.

## Testbelege

Vor jeder Implementierungsänderung: vorhandene Eventtests plus neue Regressionen ausgeführt. `phpunit-ov-events-before.txt` zeigt 13 Tests, 26 Assertions, fünf erwartete Fehler: gemischte OV-Termine, fehlende externe Links, leere Legacy-Links, ungültige Kontakt-E-Mail, unveröffentlichter KV-Rechtstext.

Finaler Befehl:

```sh
docker exec kreisverband-tests flock /tmp/gk-phpunit.lock vendor/bin/phpunit --filter 'OrtsverbandTest|EventsTest'
```

Ergebnis in `phpunit-ov-events.txt`: **23 Tests, 153 Assertions, erfolgreich**. Abgedeckt sind zusätzlich KV/OV-Fallback, Legacy-Lesepfade, sichere Protokolle, Admin-Abbruch vor Speicherung, Admin-Labels, OV-Unterseiten und Cookie-Hinweis, Kategorie + OV, UTC, Ganztag, Zeilenfaltung und Ausschluss nichtöffentlicher Termine.

`php-lint-ov-events.txt`: alle zwölf betroffenen Theme-Dateien und beide Testdateien syntaktisch korrekt. `git diff --check` erfolgreich. Die neue Datei `tests/OrtsverbandTest.php` ist PHPCS-sauber (`phpcs-ortsverband-test.txt`, leere Ausgabe, Exit 0).

Historischer Zwischenstand vor dem ausdrücklich beauftragten PHPCS-Bereinigungslauf: PHPCS über die zwölf bestehenden Theme-Dateien war **nicht grün**: `phpcs-ov-events.txt` dokumentiert 1008 Fehler/289 Warnungen, unter anderem vorhandene Formatierungs-, Dokumentations- und Nonce-Warnungen. Dies ist kein Nachweis eines vollständig behobenen PHPCS-Bestands; der Coordinator besitzt den Gesamt-Baselinebericht. Im Folgelauf wurden diese zwölf zugewiesenen Dateien und die Karten-JavaScript-Datei bereinigt; keine fremden Bereiche oder der Ruleset wurden von diesem Worker geändert. Der alte Bericht bleibt als Vorher-Beleg erhalten.

## Lokaler HTTP-Smoke-Test

Nur die separate Review-Installation auf `http://localhost:8082` verwendet diesen Workspace. Ein anfänglicher nur lesender Request an :8080 zeigte den alten Checkout und zählt ausdrücklich nicht als Validierung; dessen Daten wurden nicht verändert.

```text
GET :8082/termine/ical/?zuordnung=invalid-ov
200, Content-Type: text/calendar; charset=utf-8, 0 VEVENT
GET :8082/termine/ical/?zuordnung=ov-musterstadt
200, 1 VEVENT: OV Musterstadt: Vorstandssitzung
DTSTART:20260930T180000Z (20:00 Europe/Berlin)
GET :8082/termine/ical/
200, 7 VEVENT
```

Die Fixtures stammen aus der lokalen Demo-Installation. Adminformular-Rendering und serverseitiger Validierungsabbruch wurden durch WordPress-Integrationstests geprüft; die vollständigen interaktiven Rollen-/Editor-/Browserabläufe übernimmt der Coordinator.

## Hinweise für das deutsche Handbuch

1. Unter **Verband → Ortsverband bearbeiten** entweder eine lokale Startseite auswählen oder die externe Website unter Kontaktdaten eintragen. Eine gültige veröffentlichte lokale Seite hat Vorrang. Der Typ „Werbeseite“ bleibt als im Aufbau gekennzeichnet.
2. Unter **Impressum & Datenschutz** die eigene veröffentlichte Seite auswählen oder ausdrücklich **KV-Impressum verwenden / KV-Datenschutz verwenden** wählen. Entwurf, Papierkorb und Passwortschutz sind keine öffentlichen Linkziele. Fehlende KV-Rechtstexte durch Administratoren veröffentlichen/zuordnen lassen.
3. **Öffentliche Kontakt-E-Mail** ist eine sichtbare Funktionsadresse, kein Benutzerkonto und kein Postfachauftrag. Keine Passwörter eintragen. Die organisatorische Beantragung und Übergabe von Vorstandsadressen separat dokumentieren.
4. **Termine → Neuer Termin**: Titel, Start-/Enddatum und Uhrzeit oder „Ganztägig“, Ort, Adresse, Veranstalter, externer Link, Beitragsbild und Termin-Kategorie setzen; genau eine KV/OV-Zuordnung kontrollieren; Vorschau, veröffentlichen und bei Korrekturen aktualisieren. Kalender übernimmt die WordPress-Zeitzone; ganztägiges Enddatum ist der letzte Veranstaltungstag.
5. `[termine zuordnung="ov-musterstadt" kategorie="sitzung"]` und `[naechste_termine zuordnung="ov-musterstadt"]` filtern gezielt. Auf einer OV-Seite gilt automatisch deren eigener Bereich. Die unfiltrierte allgemeine Terminübersicht enthält weiterhin alle Bereiche.
6. Den iCal-Link der gefilterten Übersicht oder der OV-Startseite abonnieren, um nur diesen Bereich zu übernehmen. Einzeltermin-Download ist weiterhin möglich. Kalenderprogramme aktualisieren Abonnements in ihren eigenen Intervallen.

## Übergabe / Grenzen

Rollenrechte, eindeutige Zuordnung auf REST-/Speicherwegen und Medienberechtigungen werden im parallelen Rollen-Worker implementiert und geprüft; `roles.php` und `contact-form.php` wurden von diesem Worker nicht geändert. `shortcodes.php` enthält zusätzlich unabhängige Coordinator-Änderungen am Personen-Shortcode, die erhalten wurden. Vollständiger Testlauf, zweiter Review, Build/ZIP, Handbuchintegration sowie abschließende interaktive Smoke-Tests bleiben beim Coordinator. Keine automatischen Bestandsdaten- oder Postfachmigrationen; für Rollback gelten die normalen Theme-Release-/Backup-Schritte.
