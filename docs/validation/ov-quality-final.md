# OV-Qualitätsgate und erweiterte Kartenabnahme

Stand 20.09.2026, Worker `task_68a9bfddf283`, Branch `fix/geschaeftsstelle-release`. Code-Freeze dieses Workers nach erfolgreicher Schlussprüfung; keine Commits und keine produktiven Änderungen.

## Finaler Qualitätsstatus

- `docs/validation/phpcs-ov-final.txt`: **Exit 0, keine Fehler oder Warnungen** für zwölf zugewiesene PHP-Themedateien, `theme/lib/js/scripts.js` und `tests/OrtsverbandTest.php` unter `phpcs.xml.dist`.
- `docs/validation/phpunit-ov-final.txt`: **37 Tests, 218 Assertions, erfolgreich** (`OrtsverbandTest|EventsTest|ShortcodesTest`), mit gemeinsamem `flock /tmp/gk-phpunit.lock`.
- `node --check theme/lib/js/scripts.js`: erfolgreich; scoped `git diff --check`: erfolgreich.
- Die früheren `phpcs-ov-events.txt` und Erstbericht sind historische Vorher-Belege und beschreiben nicht mehr den Abschlussstand.
- Der Worker hat den Ruleset nicht geändert. Eng begrenzte, kommentierte Ausnahmen betreffen notwendige Taxonomie-/Datumsabfragen, bereits validierte Helfer-Aufrufe, lesende Filterparameter, WordPress-generierte Dropdowns/SVG, registrierte Theme-Capabilities und kompatibel beibehaltene Dateinamen/öffentliche Funktionsparameter. Keine pauschalen Datei-Ignores.

## Bedeutende Änderungen über Formatierung hinaus

**Sichere Adminverarbeitung:** Eingaben werden vor Textsanitization entslasht; fehlende Migrations-Nonce-Indizes werden geprüft, interne Weiterleitungen verwenden `wp_safe_redirect`, dynamischer Text und Attribute werden am Ausgabeort escaped. Bestehende Legacy-Funktions-/Parameter-/Dateinamen bleiben kompatibel. Schleifenlängen werden zwischengespeichert; Dokumentation und Übersetzerdetails wurden ergänzt.

**OV löschen (Review IR02):** Der Handler verlangt zusätzlich zum Nonce und zur OV-Verwaltungscapability aktuell `manage_options` und `delete_term` für den konkreten Begriff. Ein gültiger alter Administrator-Nonce ermöglicht nach Herabstufung zum OV-Admin keine fremde OV-Löschung. Vorher-Reproduktion: `phpunit-ov-delete-before.txt`; endgültige Regression im grünen Testsatz.

**OV-Bilder (Review IR04/IR05):** Neue Hero-/Kandidatenbilder müssen echte Bild-Attachments im autorisierten Bereich sein; `upload_files` ist erforderlich. Abgelehnte neue IDs lassen die bisherige Zuordnung unverändert und führen zu einem verständlichen Warnhinweis; unveränderte alte, auch anders zugeordnete Bildreferenzen bleiben erhalten. Der gemeinsame Medienpicker wird auf KV- und OV-Formularen eingebunden, und seine WordPress-Skripte werden für `gk_manage_ov` plus `upload_files` geladen, ohne `edit_theme_options` zu vergeben. Vorher-Reproduktionen in `phpunit-ov-media-before.txt`; anschließend eigene/fremde/alte Bildreferenzen, Handler und Script-Enqueue regressionsgeprüft.

## Verbindliche Link-Vorrangregel

1. Eine konfigurierte veröffentlichte, nicht passwortgeschützte lokale OV-Startseite hat Vorrang.
2. Ohne nutzbare konfigurierte ID wird eine bereits vorhandene Seite genau am OV-Slug verwendet, wenn sie diesem OV zugeordnet ist oder als unzugeordnete Altseite die OV-Startseitenvorlage verwendet. Fremde oder mehrdeutige Zuordnungen, Entwürfe und Passwortschutz werden ausgeschlossen. Keine Daten werden automatisch umgeschrieben; `get_permalink` erhält bestehende kanonische URLs.
3. Danach folgt die validierte öffentliche OV-Website. Ein OV im Aufbau ohne lokale Seite bleibt inaktiv; die ausdrücklich konfigurierte Aufbau-Kennzeichnung wird nicht allein durch eine externe Kontaktadresse aufgehoben.
4. Fehlt ein aktuelles OV-Ziel, kann ein als externer Link konfigurierter Karteneintrag seine validierte HTTP(S)-Adresse verwenden.
5. Aktuelle funktionsfähige lokale Ziele haben Vorrang vor veralteten Kartentypen wie `werbung`, `keine` oder `link`. Lokale Ziele erhalten auch bei altem Kartentyp `link` keine irreführende „Extern“-Kennzeichnung.
6. Ohne Ziel erscheinen Listeneinträge und Kartenregionen klar als **Im Aufbau**, ohne `href="#"` und ohne unnötigen Tastaturfokus.

Beide Vorher-Reproduktionen wurden vor den funktionalen Kartenänderungen in `phpunit-map-before.txt` erfasst. Die Tests prüfen tatsächliches SVG und responsive HTML, externe/sichere Protokolle, alte Direktseiten, falsche Zuordnungen, veröffentlichte Seiten trotz alter Aufbau-Typen und deaktivierte Einträge.

## Tastatur und Mobilgeräte

Die SVG-Karte ist eine Gruppe interaktiver Elemente statt eines für Assistenztechnik atomaren Bildes. Echte Links bleiben mit Tab/Enter bedienbar; Tastatur- und modifizierte Klicks behalten die native Navigation. Mobile Pointer-Klicks öffnen einen beschrifteten Dialog mit echtem Ziel-Link, Fokusübergabe, Tab-Zyklus, Escape und Fokusrückgabe. Dialog-CTA bekommt erst beim Öffnen ein reales `href`; geschlossene und inaktive Elemente erzeugen keine Platzhalter-Linkziele. Mehrere responsive Karten instanziieren ihre Handler getrennt. Keine CSS-Änderung war erforderlich.

## Koordinator: lokale Abnahme und Selektoren

Die synthetische Koordinator-Fixture liegt auf `http://localhost:8082/ov-linktest/`; Sollziele stehen lokal in `/tmp/gk-map-fixtures.json`. Kein Produktionsinhalt wurde für diese Tests in Git übernommen. Ein lesender HTML-Abgleich bestätigte identische Ziele in Liste und SVG: zehn lokale Gemeinden einschließlich der Altseite Gilching ohne Homepage-ID und Herrsching trotz altem Kartentyp `werbung`, ein externer Testlink, drei nicht verlinkte Aufbau-Gemeinden.

- Liste: `.gk-ov-chip[data-ov-slug]`; Link ist `a[href]`, Aufbau ist `div` ohne `href`/`tabindex`.
- SVG: `#kreiskarte-municipalities > [data-ov-slug]`; Link ist `a[href]`, Aufbau ist `g` ohne `tabindex`.
- Auf Mobilbreite: `.gk-ov-toggle` wechselt zur Karte, Pointer-Klick auf echten SVG-Link öffnet `.gk-ov-sheet[role="dialog"]`, CTA `.gk-ov-sheet__cta[href]`, Abbruch `.gk-ov-sheet__close`.
- Tastatur: Tab auf echten SVG-Link, Enter folgt dem gleichen Ziel direkt; inaktive Regionen erhalten keinen Fokus.
- Medienpicker als OV-Admin: Verband → eigenes OV-Formular → Bild wählen; `wp.media` muss vorhanden sein, die Bibliothek nur den autorisierten Bereich zeigen. Fremde per Request gefälschte IDs führen nach Speicherung zum sichtbaren Warnhinweis.

Der vollständige integrierte Testlauf, eigenständige Browser-Klickmatrix, Release-Build und GitHub-Release verbleiben beim Koordinator. Die Live-Installation erfolgt laut Nutzer separat manuell.
