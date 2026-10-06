# Prüfung konfigurierbarer Verbandsebenen und Karten

Stand: 6. Oktober 2026, Release 0.10.0.

## Konfiguration und bestehende Installationen

Die WordPress-Tests prüfen die verpflichtende Auswahl beider Ebenen, eigene Bezeichnungen, unabhängige Anzeige- und URL-Präfixe sowie die Erhaltung einer zuvor gültigen Konfiguration bei unvollständigen Eingaben. Das Löschen eines überschriebenen Anzeigepräfixes stellt das ursprüngliche Kürzel wieder her.

Eine Umstellung von Kreisverband/Ortsverband auf Bezirksverband/Kreisverband erhält die bestehenden Zuordnungs-IDs, Slugs und Kontobereiche. Ein eingeschränktes Konto kann weiterhin Inhalte des eigenen Bereichs bearbeiten und erhält keinen Zugriff auf fremde Bereiche oder allgemeine Einstellungen. Rollenanzeigen und Seitenvorlagennamen verwenden die konfigurierten Kürzel.

## Reale Kartengebiete

Die Suche wurde über Nominatim ausgeführt. Die Grenzen wurden über einen öffentlichen Overpass-Spiegel geladen und mit derselben Verarbeitung wie im Kartengenerator erzeugt. Öffentliche Kartendienste waren während der Prüfung teilweise ausgelastet; ihre dauerhafte Verfügbarkeit ist nicht durch diese Ergebnisse zugesichert.

| Gebiet | OSM-Relation | Automatisch gewählte Unterteilung | Erzeugte Gebiete |
|---|---|---|---:|
| München | 62428 | Stadtbezirke, Ebene 9 | 25 |
| Landkreis München | 62580 | Gemeinden, Ebene 8 | 29 |
| Landkreis Starnberg | 62458 | Gemeinden, Ebene 8 | 14 |
| Oberbayern | 2145274 | Kreise / kreisfreie Städte, Ebene 6 | 23 |

Für alle vier Fälle wurden eindeutige Gebietskennungen, geschlossene SVG-Pfade und endliche Koordinaten geprüft. Die öffentliche Ausgabe im WordPress-Theme enthält jeweils dieselbe Zahl von Gebietspfaden. Ein synthetisch zugeordnetes lokales Seitenziel, der Zustand **Im Aufbau** für fehlende Ziele und der OpenStreetMap-Quellenhinweis wurden ebenfalls geprüft. Die Geometrien von München und Oberbayern wurden zusätzlich als SVG gerendert und visuell kontrolliert.

Die JavaScript-Tests decken manuelle und automatische Ebenen, mehrteilige Grenzen einschließlich einer Exklave außerhalb des größten Teilpolygons, gleiche Gebietsnamen, konfigurierbare URL-Präfixe, unvollständige Antworten und offene Grenzen ab. Beim Neuladen bleiben vorhandene Ziele und Slugs über die OSM-Kennung erhalten; Zuordnungen eines anderen Gebiets werden nicht übertragen. Gemeindefreie Landflächen werden nicht als Wasser eingefärbt, und Gemeindeschlüssel von Kreisen führen nicht zum Ausschluss dieser Kreise.

## Automatisierte Prüfungen

- WordPress 6.9.4 / PHP 8.5: **131 Tests, 692 Assertions**, erfolgreich.
- JavaScript: **6 Tests**, erfolgreich; Syntaxprüfung aller Theme-JavaScript-Dateien erfolgreich.
- PHP-Syntax: **97 Theme-Dateien**, erfolgreich.
- WordPress-Coding-Standards und PHP-Kompatibilitätsprüfung für Theme und neue Konfigurationstests: erfolgreich.
- Prüfung auf fehlerhafte Leerzeichen mit `git diff --check`: erfolgreich.

Die automatisierten Prüfungen können mit `make test`, `make js-check` und `make phpcs` wiederholt werden. Die WordPress-Tests benötigen eine eingerichtete Testdatenbank und die WordPress-Testsuite.

Der zusätzliche Live-Test läuft mit `node bin/smoke-map.mjs`. Er benötigt Node.js mit `fetch` und eine Netzwerkverbindung. `NEURG_MAP_CACHE` legt optional einen lokalen Cache außerhalb des Repositories fest. Mit `NEURG_OVERPASS_ENDPOINTS` können bei Bedarf alternative Overpass-Interpreter als kommaseparierte URLs angegeben werden. Die erwarteten Gebietszahlen beziehen sich auf den Prüfstand; spätere OSM-Änderungen sind vor einer Anpassung dieser Erwartungen fachlich zu prüfen.

Auf einer separaten WordPress-Installation wurde das ZIP als reguläres Update von 0.9.0 auf 0.10.0 installiert. Bestehende Slugs, Kontozuordnungen, eingeschränkte Redaktionsrechte und gespeicherte Polygonkarten bleiben erhalten. Die Wiederherstellung des Datei- und Datenbankbackups auf 0.9.0 war erfolgreich.

Die zusätzliche Browserprüfung deckt die Pflichtauswahl, das Ein-/Ausblenden eigener Bezeichnungen, das Speichern der Anzeige- und URL-Präfixe sowie das Erzeugen und Speichern aller vier Karten ab. Dafür wurden die zuvor geladenen echten OSM-Antworten in den Browserablauf eingespeist. Die Unterteilung bleibt bei einem erneuten Aufruf des Generators ausgewählt. Eine produktive WordPress-Installation wurde nicht verändert.
