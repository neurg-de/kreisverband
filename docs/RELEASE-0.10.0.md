# Version 0.10.0

Neurg unterstützt frei benannte Hauptverbände und Untergliederungen auf höchstens zwei redaktionellen Ebenen. Die Administration wählt beide Bezeichnungen zu Beginn im **Verbands-Setup**, beispielsweise Kreisverband → Ortsverbände, Stadtverband → Stadtteilgruppen, Bezirksverband → Kreisverbände oder Regionsverband → Ortsverbände.

## Bezeichnungen und Präfixe

- Vorgegebene Verbandsarten frei kombinieren oder eigene Einzahl, Mehrzahl und Kürzel angeben.
- Anzeigepräfixe für beide Ebenen unabhängig überschreiben. Navigation, Rollenanzeigen, Seitenvorlagennamen und automatisch angelegte Namen berücksichtigen diese Einstellung.
- Das URL-Präfix neuer Untergliederungen gesondert konfigurieren. Individuelle vollständige Slugs bleiben möglich.
- Vorhandene Inhalte, Slugs, Kontozuordnungen und Redaktionsrechte erhalten. Bestehende individuelle Namen und Headertexte werden weiterhin redaktionell gepflegt.

Bei bisherigen Installationen bleibt zunächst die Kreisverband-/Ortsverband-Anzeige erhalten. Die Administration muss die beiden Ebenen ausdrücklich auswählen, damit das Setup wieder als abgeschlossen gilt. Es gibt keine automatische Umbenennung bestehender URLs. Das GitHub-Repository `kreisverband`, das Theme-Paket `neurg-kreisverband`, technische Rollenkennungen und Shortcodes behalten ihre historischen Namen.

## Verbandskarte

Die Kartengenerierung unterstützt Gemeinden, Stadtbezirke und größere Untergebiete. Eine automatische Auswahl berücksichtigt die verfügbaren OpenStreetMap-Verwaltungsebenen; die Administration kann eine Ebene ausdrücklich wählen. Mehrere geografische Gebiete können derselben Untergliederung zugeordnet werden. Verwaltungsgrenzen müssen vor der Übernahme auf ihre Eignung für die Verbandsstruktur geprüft werden.

- Mehrteilige Grenzen, Exklaven und innere Aussparungen bleiben als SVG-Pfade erhalten.
- Beim Neuladen desselben Gebiets mit derselben Unterteilung bleiben gespeicherte Zuordnungen und Slugs über die OSM-Kennung erhalten, auch bei Namens- oder Präfixänderungen. Ältere Karten ohne diese Kennungen verwenden den bisherigen Titel-/Slug-Abgleich.
- Offene Grenzen und unvollständige Kartendienst-Antworten werden als Fehler gemeldet. Bei Nichterreichbarkeit wird ein alternativer Overpass-Dienst versucht.
- Gemeindefreie Landflächen werden nicht pauschal als Wasser eingefärbt. Gemeindeschlüssel auf Kreisebene führen nicht zum Ausschluss der Kreise.
- Bestehende Polygonkarten bleiben darstellbar. Die öffentlichen Karten- und Listenlinks verwenden weiterhin dieselben Zielregeln; ohne nutzbares Ziel bleibt **Im Aufbau** ohne Link sichtbar. Die SVG-Karte zeigt einen OpenStreetMap-Quellenhinweis.

## Prüfungen und Update

131 WordPress-Tests mit 692 Assertions und sechs JavaScript-Tests sind erfolgreich. PHP-Syntax, WordPress-Coding-Standards, JavaScript-Syntax, Sass-Build, Versionsabgleich und ZIP-Struktur wurden geprüft. Die echten OSM-Gebiete München (25 Stadtbezirke), Landkreis München (29 Gemeinden), Landkreis Starnberg (14 Gemeinden) und Oberbayern (23 Kreise / kreisfreie Städte) wurden erfolgreich erzeugt und öffentlich im Theme gerendert.

Das Release-ZIP wurde auf einer separaten WordPress-Testinstallation als reguläres Update von 0.9.0 auf 0.10.0 installiert. Bestehende Zuordnungen, Rollenrechte und gespeicherte Kartendaten bleiben erhalten. Datei- und Datenbankbackup wurden anschließend erfolgreich auf 0.9.0 zurückgespielt. Die Setup-Auswahl und Kartengenerierung wurden zusätzlich im Browser geprüft; für die Kartenabläufe wurden die zuvor geladenen echten OSM-Daten verwendet.

Die [Konfigurationsanleitung](VERBANDSEBENEN.md), das [Benutzerhandbuch](BENUTZERHANDBUCH.md) und der [Prüfbericht](validation/association-levels.md) erläutern die Einrichtung und Grenzen. Vor einem Update das eigene Datei- und Datenbankbackup samt Wiederherstellungsweg prüfen und das ZIP auf einer Testkopie installieren. Eine GitHub-Veröffentlichung aktualisiert keine bestehende WordPress-Installation.
