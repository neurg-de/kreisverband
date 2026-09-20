# Release 0.7.0 – Geschäftsstelle und Ortsverbände

Geprüfter Stand vom 20.09.2026. Ausgangspunkt: `ac3507de322a92b1e324421df77da4f6a8bd0113`
(0.6.2), Arbeitsbranch `fix/geschaeftsstelle-release`, Veröffentlichung über
`dev` → Squash-Commit auf `main` → Tag `v0.7.0` → GitHub Actions.

Die GitHub-Veröffentlichung stellt das installierbare Theme-ZIP und `SHA256SUMS`
bereit. Die Installation auf **gruene-starnberg.de erfolgt manuell**.
Es wurde keine Live-Installation verändert und keine Nachricht an Dritte versendet.

## Änderungen

- Redaktionelle Rollen können berechtigte Inhalte veröffentlichen, in den
  Papierkorb verschieben und wiederherstellen, aber nicht dauerhaft löschen.
  Fremde nichtöffentliche Inhalte und direkte REST-Anfragen bleiben gesperrt.
  Das Rollenupgrade erhält Benutzerzuweisungen, insbesondere `gk_kvautor_ov`
  für die Geschäftsstelle; es vergibt ihr keine Administratorrechte.
- Administratoren erhalten eine datensparsame Rollen-/Zuständigkeitsübersicht
  mit Nonce- und Capability-Prüfungen. OV-Admins bearbeiten nur ihren OV und
  erhalten keine globalen Theme-/Customizerrechte.
- OV-Listen und Kreiskarte nutzen gemeinsame, datenbasierte Linkziele:
  veröffentlichte lokale Seite, sonst hinterlegte externe Website, sonst
  „Im Aufbau“. Alte `full`-Werte und vorhandene `/ov-xy/`-Seiten werden berücksichtigt.
  Kartenziele, mobile Einstiege, Fokus und sichtbarer Aufbauzustand werden geprüft.
- Pflichtseiten sind veröffentlichte, nicht passwortgeschützte Seiten;
  OV-Einstellungen fallen auf die KV-Konfiguration zurück. Öffentliche
  Kontaktadressen werden validiert. Postfächer werden organisatorisch eingerichtet.
- Termine und iCal werden nach Zuständigkeit abgegrenzt. Kalenderexporte
  berücksichtigen Zeitzone, ganztägige Termine, Textescaping und Zeilenfaltung.
- `[newsletter_anfrage]` und die Seitenvorlage „Mach mit“ senden eine bewusst
  bestätigte Interessensbekundung an `info@gruene-starnberg.de`. Kein automatisches
  Abonnement, keine Speicherung des Nachrichteninhalts im Theme. Formularbindung,
  Nonce, Honeypot, begrenzte Versandrate und serverseitige Validierung schützen
  den Versand; ein Mailfehler wird nicht als Erfolg ausgegeben.
- Mediathek, Uploads und neue Bildauswahl richten sich nach `gk_zuordnung`,
  nicht nach Autor. Die Geschäftsstelle darf bereichsübergreifend arbeiten.
  Vorhandene Bildreferenzen bleiben erhalten. Ein Administrator kann Altbestand
  einzeln prüfen, zuordnen und mit Konfliktprüfung zurücknehmen.
- Bestehende Coding-Standard-Verstöße wurden behoben, einschließlich fehlender
  Ausgabe-Escapes und Nonce-Prüfungen. Die breite Formatbereinigung erklärt den
  größeren Diff. Drei generierte Sass-Dateien und ausschließlich der moderne
  Karten-Generator-JavaScriptcode sind begründet von PHPCS ausgenommen;
  Sass-Build, Node-Syntaxprüfung und Verhaltenstests prüfen diese Dateien.
  Bei den statischen HTML-Demovorlagen ist ausschließlich die PHP-Parsermeldung
  „kein PHP-Code gefunden“ ausgenommen; alle tatsächlichen Regeln bleiben aktiv.
- Der ZIP-Build prüft alle vier Versionsangaben und die Verzeichnisstruktur.
  Das Theme startet auch ohne die absichtlich nicht ausgelieferte Demo-Datei
  bei `WP_DEBUG=true`. CI prüft PHP, PHPUnit, PHPCS, JavaScript und ZIP-Build.

## Handbuch und unabhängige Prüfung

Das [deutsche Benutzerhandbuch](BENUTZERHANDBUCH.md) erklärt die gesamte
Redaktionsoberfläche, Rollen, OV-Verlinkung, Bilder, Termine, Formulare, Pflichtseiten,
Menüs, Homepage, Karte, Blöcke/Shortcodes, SEO, Spenden und typische Rücknahmen.
Es enthält konkrete Screenshot-Aufnahmehinweise mit synthetischen Daten.

Der [unabhängige Reviewbericht](validation/INDEPENDENT-REVIEW.md) dokumentiert
reproduzierte Befunde und erneute Prüfungen. Der Release-Commit und der Tag werden erst nach bestandenem Schlussreview
veröffentlicht. Die fünf unabhängig gefundenen Probleme betrafen private Lesezugriffe,
OV-Löschen nach Rollenwechsel, geschützte Datenschutzseiten, fremde Homepage-Bilder
und den fehlenden OV-Bilddialog. Für alle wurden Korrekturen und Regressionstests
umgesetzt.

## Testumgebung und Nachweise

Die vorhandene Entwicklungsinstallation auf Port 8080 wurde nicht aktualisiert.
Getestet wird mit PHP 8.3.31, WordPress 6.9.4 und separaten Testdatenbanken:

- PHPUnit: isolierter Container `kreisverband-tests`, Datenbank `wordpress_test`.
- Praktische Redaktion: `http://localhost:8082`, nur synthetische Inhalte und Konten.
- Paketinstallation: `http://localhost:8083`, separates Dateivolume und Datenbank.
  Hier wird das ZIP regulär installiert, ohne Bind-Mount zum Quellverzeichnis.
- Lokaler Mail-Interceptor verhindert sämtliche externen Testmails.

Die erste GitHub-Prüfung bestand PHPUnit, PHP-Syntax und ZIP-Build; PHPCS meldete
wegen deaktivierter PHP-Kurztags die zwölf reinen HTML-Demovorlagen. Die präzise
Parser-Ausnahme wurde mit derselben PHP-Einstellung nachgeprüft, ohne Theme-Dateien
oder das bereits geprüfte ZIP zu verändern.

Die tatsächlichen Befehlsausgaben stehen unter [validation/](validation/).
Baseline-Dateien dokumentieren absichtlich den fehlerhaften Zustand vor der
Korrektur; maßgeblich sind die abschließenden Ergebnisse. Temporäre Test-Nonces
werden in gespeicherten Fehlermeldungen geschwärzt.

| Prüfung | Nachweis |
|---|---|
| PHPUnit: **97 Tests, 413 Assertions, PASS** | [phpunit-final.txt](validation/phpunit-final.txt) |
| `make phpcs`: **0 Fehler, 0 Warnungen** | [phpcs-full.txt](validation/phpcs-full.txt) |
| PHP- und JavaScript-Syntax | [php-syntax.txt](validation/php-syntax.txt), [javascript-syntax.txt](validation/javascript-syntax.txt) |
| Mobile Hauptnavigation im installierten ZIP: **PASS, 390 px, Ziel HTTP 200** | [stage-mobile-navigation.json](validation/stage-mobile-navigation.json) |
| Sass / `make zip` | [css-build.txt](validation/css-build.txt), [build.txt](validation/build.txt) |
| Authentifizierte HTTP-Lebenszyklen und Zuständigkeiten | [http-lifecycle.txt](validation/http-lifecycle.txt) |
| Alle konfigurierten Gemeinden: direkte Route, HTTP-Status, OV-Kontext | [http-map-routes.json](validation/http-map-routes.json) |
| Tatsächliche Karten-/Listenklicks, Mobilgerät und Tastatur: **98/98 PASS** | [Browsermatrix](validation/independent-browser-results.json), [5 weitere Fokus-/Medienprüfungen](validation/independent-accessibility-results.json), [HTTP und sichtbarer Kontext](validation/independent-route-http.json) |
| Kalender, Beitragsbild-Upload und Newsletter | [Review](validation/INDEPENDENT-REVIEW.md), [Upload](validation/browser-media-upload.json), [Newsletter](validation/http-newsletter.txt) |
| Generator-Geometrie und AJAX-Fehlerverhalten | [generator-smoke.txt](validation/generator-smoke.txt) |
| Reguläres ZIP-Update und Backup-Wiederherstellung | [stage-update-rollback.txt](validation/stage-update-rollback.txt) |

Lokale Containerkommandos führen dieselben Projektziele aus; ein temporärer
`composer`-Wrapper ruft Composer im PHP-Container auf. Alle PHPUnit-Läufe nutzen
eine gemeinsame Dateisperre, damit parallele Prüfungen die Testdatenbank nicht
gegenseitig zurücksetzen.

Das reguläre Paketupdate von 0.6.2 auf 0.7.0 war erfolgreich. Der anschließende
Rollback stellte Dateien und Datenbank wieder her: Version 0.6.2, ursprünglicher
Rollenstand und unveränderte Geschäftsstelle-Zuweisung wurden kontrolliert;
Startseite, OV, Termine, Pflichtseiten und Anmeldung lieferten HTTP 200.
Danach wurde das endgültige geprüfte Paket erneut installiert; die authentifizierten
HTTP-Lebenszyklen wurden nochmals erfolgreich geprüft.

Lokales geprüftes ZIP: `neurg-kreisverband-0.7.0.zip`, SHA-256:

```text
0eb1175cedfbaf1e8ad6e656864e37db4c4c26dd61a9066fc8bbbc3fc196c02f
```

Die GitHub-Actions-Datei wird erneut gebaut und kann wegen ZIP-Zeitstempeln eine
andere Prüfsumme haben. Für den heruntergeladenen Release gelten dessen
mitgelieferte `SHA256SUMS`; nach Veröffentlichung werden diese zusätzlich geprüft.

## Daten und Migration

Ein Theme-Update ändert keine Benutzerrolle der Geschäftsstelle und führt keine
Massenmigration von Medien durch. Bisher nicht zugeordnete Medien können für
OV-Nutzer zunächst unsichtbar bleiben; die Administration prüft sie unter dem
Medien-Zuordnungswerkzeug. Vor jeder Änderung die Vorschau ansehen, danach den
geprüften Einzelfall anwenden. Eine Rücknahme stellt die vorherige Zuordnung
wieder her, sofern diese noch existiert und nicht zwischenzeitlich geändert wurde.
Dateien und vorhandene Beitragsbild-IDs werden dabei nicht gelöscht oder verschoben.

Die Karte kann lokal in `theme/lib/data/kreiskarte.json` angepasst worden sein.
Diese Datei ist zusätzlich zur Datenbank zu sichern; ein reguläres Theme-Update
ersetzt Theme-Dateien. Eigene Kartendaten anschließend kontrolliert wiederherstellen
und alle Verknüpfungen gegen die OV-Daten prüfen.

## Manueller Live-Schritt

1. Release-ZIP und `SHA256SUMS` aus dem GitHub-Release herunterladen und mit
   `shasum -a 256 -c SHA256SUMS` prüfen. Ausschließlich das Theme-ZIP installieren,
   nicht das automatisch angebotene Quellcodearchiv.
2. Vollständige Dateien, Datenbank und angepasste Kartendateien sichern; Ort,
   Zeitpunkt, Wiederherstellungszugang und Wartungsfenster festhalten.
3. Eine aktuelle geschützte Staging-Kopie von gruene-starnberg.de mit diesen
   Daten erstellen, ausgehende E-Mails abfangen, ZIP als reguläres Theme-Update
   einspielen und die folgenden Prüfungen durchführen. Die hier dokumentierte
   synthetische lokale Staging-Prüfung ersetzt diesen Test der tatsächlichen
   Konfiguration nicht.
4. Rücknahme mit den gesicherten Dateien **und** der Datenbank praktisch prüfen.
   Erst danach das geprüfte ZIP im WordPress-Backend auf Live hochladen und das
   bestehende Theme ersetzen. Keine einzelnen PHP-Dateien in Live überschreiben.
5. Nach dem Update Version 0.7.0 und unveränderte Rolle `gk_kvautor_ov` der
   Geschäftsstelle prüfen. Startseite, KV-/OV-Seiten, Datenschutz, Impressum,
   Beiträge, Personen, Termine und iCal, Medien, Formulare und mobile Navigation
   öffnen; mit Geschäftsstelle einen Entwurf, Papierkorb und Wiederherstellung testen.
6. **Jede aktuell konfigurierte Gemeinde einzeln anklicken**, sowohl in der
   Gemeinde-/OV-Liste als auch in der Karte. Pro Einstieg Gemeinde, erwartetes
   Ziel, tatsächliche Ziel-URL, HTTP-Status und sichtbaren OV-Kontext festhalten.
   Lokale `/ov-xy/`-Adressen zusätzlich direkt aufrufen. Externe Ziele müssen
   inhaltlich dem OV entsprechen. Einträge ohne Ziel müssen „Im Aufbau“ zeigen
   und dürfen keinen Scheinlink besitzen. Auf Mobilgerät und mit Tab/Enter wiederholen.
7. Bei Fehlern Update stoppen bzw. Dateien und Datenbank wiederherstellen,
   Cache leeren, alte Version und Erreichbarkeit prüfen. Keine weiteren
   redaktionellen Änderungen während einer Rücknahme zulassen.

Die öffentlichen Live-Adressen wurden nur lesend zur Einordnung von Floras
Fehlerbericht geprüft. Dabei war mindestens eine funktionierende direkte OV-Seite
über den Einstieg nicht erreichbar; außerdem existiert eine konfigurierte
`example.com`-Platzhalteradresse. Diese Adresse muss die Administration durch die
fachlich richtige OV-Website ersetzen. Die Software kann das gewünschte externe
Ziel nicht erraten. **Kein Live-Smoke-Test nach Update durchgeführt**, da das
Live-Update gemäß Auftrag manuell erfolgt.

Newsletter-Verteilereintrag, Einrichtung/Übergabe von Postfächern, rechtliche
Freigabe der Pflichtseitentexte und Korrektur örtlicher Inhalte bleiben Aufgaben
der Geschäftsstelle bzw. Administration. Das Theme automatisiert diese Schritte nicht.
