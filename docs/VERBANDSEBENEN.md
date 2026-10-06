# Verbandsebenen und Produktbeschreibung

Neurg verwaltet höchstens zwei redaktionelle Ebenen: einen Hauptverband und mehrere Untergliederungen. Eine Website kann zunächst ausschließlich den Hauptverband zeigen; Untergliederungen können später hinzukommen.

## Einrichtung

Die Administration wählt unter **Verbands-Setup** zuerst die Bezeichnung beider Ebenen. Ohne diese Auswahl gilt die Ersteinrichtung nicht als abgeschlossen. Bestehende Installationen zeigen bis zur Auswahl weiter die bisherigen Standardbegriffe Kreisverband und Ortsverband.

Die Auswahl enthält Kreis-, Stadt-, Bezirks-, Regions-, Regional-, Landes-, Bundes-, Orts- und Gemeindeverband sowie Ortsgruppe, Stadtteilgruppe, Regionalgruppe, Bezirksgruppe, Ortsverein und Unterbezirk. Bei **Eigene Bezeichnung** sind Einzahl, Mehrzahl und Kürzel erforderlich. Die Auswahl legt keine allgemeingültige politische Rangfolge fest.

Die Terminologie unterscheidet sich nach Partei und Landesverband. Beispielsweise unterscheidet die [Satzung der Grünen Bayern](https://www.gruene-bayern.de/partei/satzungen-und-co/satzung/) Bezirks-, Kreis- und Ortsverbände. Die [Satzung der Grünen Niedersachsen](https://gruene-niedersachsen.de/wp-content/uploads/sites/1/2024/11/Satzung_LV-Nds_241102-1.pdf) kennt außerdem die Bezeichnungen Stadt-, Regions- und Regionalverband. Deshalb wählt die Installation beide Namen unabhängig voneinander und bildet jeweils einen Ausschnitt mit zwei Ebenen ab.

| Beispiel | Hauptverband | Untergliederungen | Standardkürzel |
|---|---|---|---|
| Landkreis | Kreisverband | Ortsverbände | KV / OV |
| Stadt | Stadtverband | Stadtteilgruppen | SV / STG |
| Bezirk | Bezirksverband | Kreisverbände | BV / KV |
| Region | Regionsverband | Ortsverbände | RV / OV |

**Anzeigepräfix / Kürzel** kann für beide Ebenen unabhängig vom ausgewählten Typ überschrieben werden. Es wird unter anderem in Rücklinks, Rollenanzeigen, Seitenvorlagennamen und neuen automatisch angelegten Verbandsnamen verwendet. Leer verwendet das Kürzel des Typs.

**URL-Präfix neuer Untergliederungen** steuert automatisch erzeugte Slugs. Beispielsweise ergibt `stadtteil` mit „Musterort“ den Slug `stadtteil-musterort`. Leer verwendet das Anzeigepräfix in URL-Schreibweise. Im Formular einer Untergliederung kann die Administration weiterhin einen eigenen vollständigen Slug angeben.

Die Einstellungen können später wieder unter **Verbands-Setup** geändert werden. Vorhandene Slugs, Inhalte, Kontozuordnungen und Rechte werden dadurch nicht umbenannt oder erweitert. Bestehende individuelle Verbandsnamen und Headertexte werden redaktionell angepasst. Für eingeschränkte Konten muss der Benutzername weiterhin dem tatsächlichen Zuordnungsslug entsprechen.

Die historischen technischen IDs, Rollenkennungen, Shortcodes, Template-Dateien bleiben bestehen. Das GitHub-Repository heißt weiterhin `kreisverband`, das Theme-Paket `neurg-kreisverband`. Der Hauptbereich behält intern den Zuordnungsslug `kreisverband`, auch wenn die Anzeige „Bezirksverband“ lautet.

## Verbandskarte

Unter **Verband → Verbandskarte** eine Verwaltungsgrenze suchen und die Unterteilung wählen. **Automatisch** bevorzugt Bundesländer für ein Bundesgebiet, Kreise für Landes- oder Bezirksgebiete und Gemeinden für Kreisgebiete. Wo diese Ebene fehlt, wird eine verfügbare tiefere Ebene gewählt, beispielsweise Stadtbezirke für eine kreisfreie Stadt. Die Unterteilung kann ausdrücklich auf Gemeinden, Stadtbezirke, Stadtteile oder andere verfügbare Verwaltungsgrenzen gesetzt werden.

Die Karteneinteilung und die Verbandsbezeichnungen sind unabhängig. Mehrere geografische Gebiete können derselben Untergliederung zugeordnet werden. Eine dritte redaktionelle Ebene entsteht dadurch nicht. Abweichende Parteigrenzen müssen über passende Zuordnungen berücksichtigt werden; sie werden nicht aus der Bezeichnung eines Verbands abgeleitet.

Die Vorschau vor der Übernahme auf Vollständigkeit und passenden Zuschnitt prüfen. OpenStreetMap-Grenzen und Ebenen unterscheiden sich regional. Mehrteilige Gebiete und innere Aussparungen werden als SVG-Pfade erhalten. Gemeindefreie Landflächen werden nicht pauschal als Wasser dargestellt. Fehlende oder unvollständige Antworten der Kartendienste werden als Fehler gemeldet.

Beim Neuladen desselben Gebiets mit derselben Unterteilung werden vorhandene Zuordnungen über die OSM-Kennung erhalten, auch wenn sich Namen oder das Präfix für neue Einträge geändert haben. Bei älteren Karten ohne OSM-Kennungen ist nur der bisherige Vergleich von Gebietstitel und Slugs möglich. Beim Wechsel des Gebiets oder der Unterteilung die Zuordnungen neu setzen. Die Karte verwendet weiterhin die vorhandenen Link-, Termin- und Bereichsregeln.

Öffentlich gespeicherte Karten benötigen keine laufende Verbindung zum Kartendienst. Die Administration nutzt Nominatim und Overpass beim Erzeugen bzw. Neuladen. Deren Verfügbarkeit und Vollständigkeit sind Voraussetzung für die Generierung. Die öffentliche SVG-Karte zeigt **© OpenStreetMap-Mitwirkende** mit einem Link auf <https://www.openstreetmap.org/copyright>.

Die geprüften Gebiete und technischen Prüfungen sind im [Prüfbericht](validation/association-levels.md) dokumentiert.

## Textvorschlag für neurg.de

**Seitentitel:** Neurg – das WordPress-Theme für Grüne Verbände

**Einstieg:** Ein Theme. Euer Verband. Alle Untergliederungen.

**Beschreibung:** Neurg bringt euren Grünen Verband und seine Untergliederungen auf eine gemeinsame WordPress-Website. Kreisverband mit Ortsverbänden, Stadtverband mit Stadtteilgruppen oder Bezirksverband mit Kreisverbänden: Ihr wählt die Bezeichnungen und Präfixe im Einrichtungsassistenten. Jeder Bereich erhält eigene Inhalte und klar abgegrenzte Redaktionsrechte.

**Einrichtung:** WordPress bereitstellen → Theme installieren → Verbandsebenen auswählen → Untergliederungen anlegen.

**Funktionsbeschreibung:** Eigene Startseiten, Teams, Beiträge und Termine für eure Untergliederungen – gemeinsam verwaltet in einer WordPress-Installation. Beginnt mit dem Hauptverband und ergänzt weitere Bereiche, sobald sie bereit sind.

**Kartenbeschreibung:** Euer Verbandsgebiet auf einen Blick. Die interaktive Verbandskarte verwendet Verwaltungsgrenzen aus OpenStreetMap. Wählt je nach Gebiet Gemeinden, Stadtbezirke oder größere Untergebiete und ordnet sie euren Untergliederungen zu. Prüft den verfügbaren Zuschnitt in der Vorschau.

**Frage: Warum heißt das GitHub-Repository weiterhin kreisverband?** Das Projekt begann als Theme für Kreisverbände. Das Repository `kreisverband` und das Theme-Paket `neurg-kreisverband` behalten ihre historischen Namen; die beiden Verbandsebenen und ihre Bezeichnungen sind heute konfigurierbar.

Die übrigen Produkttexte sollten „Hauptverband“ und „Untergliederungen“ verwenden, wenn keine konkrete Beispielkombination gemeint ist. Eine pauschale Unterstützung beliebig vieler Ebenen oder jeder administrativen Gebietseinteilung wäre durch die Umsetzung nicht gedeckt. Aussagen über Kartenlinks müssen den tatsächlichen Regeln entsprechen: Ohne erreichbare lokale Seite oder gültiges externes Ziel steht ein Gebiet als **Im Aufbau** ohne Link in der Karte.

Die beschriebenen Funktionen sind ab Release 0.10.0 verfügbar. Frühere Releases einschließlich 0.9.0 enthalten diese Erweiterung nicht.
