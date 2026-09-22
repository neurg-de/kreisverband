# Benutzerhandbuch für Redaktion und Administration

**Neurg Kreisverband · Release 0.7.1 · Stand: 22. September 2026**

Dieses Handbuch erklärt die Arbeit mit der Website des Kreisverbands und seiner Ortsverbände. Es beschreibt den Quellcode für Release 0.7.1. Es bestätigt weder eine Installation auf der Live-Website noch die dortige Einrichtung von Seiten, E-Mail-Versand, Plugins oder Benutzerkonten. Vor dem Einsatz sind die Abläufe auf einer Testkopie mit den tatsächlich vergebenen Rechten zu prüfen.

Die beschriebenen bereichsübergreifenden Redaktionsaufgaben setzen die Rolle **„KV-Autor (mit OV-Zugang)“ (`gk_kvautor_ov`)** voraus. Die tatsächlich zugewiesene Rolle prüft die Administration. Für die tägliche Redaktion sind keine Administratorrechte erforderlich. Änderungen an globalen Einstellungen, Benutzerkonten oder der technischen Installation übernimmt die Administration.

## Wegweiser

1. [Anmelden, Profil und Sicherheit](#1-anmelden-profil-und-sicherheit)
2. [Rollen und Zuständigkeiten](#2-rollen-und-zuständigkeiten)
3. [Dashboard und Navigation](#3-dashboard-und-navigation)
4. [Beiträge: Entwurf, Veröffentlichung und Papierkorb](#4-beiträge-entwurf-veröffentlichung-und-papierkorb)
5. [Seiten und Menüs](#5-seiten-und-menüs)
6. [Kreisverband, Ortsverbände und externe Websites](#6-kreisverband-ortsverbände-und-externe-websites)
7. [Termine und iCal](#7-termine-und-ical)
8. [Personen, Abteilungen und Zuordnungen](#8-personen-abteilungen-und-zuordnungen)
9. [Medien, Beitragsbilder und Altbestand](#9-medien-beitragsbilder-und-altbestand)
10. [Datenschutz und Impressum](#10-datenschutz-und-impressum)
11. [Newsletter- und Kontaktanfragen](#11-newsletter--und-kontaktanfragen)
12. [E-Mail-Adressen für neue Vorstände](#12-e-mail-adressen-für-neue-vorstände)
13. [Homepage und Kreiskarte](#13-homepage-und-kreiskarte)
14. [Shortcodes und Blöcke zum Nachschlagen](#14-shortcodes-und-blöcke-zum-nachschlagen)
15. [SEO, Social Media und Vorschaubilder](#15-seo-social-media-und-vorschaubilder)
16. [Spenden und Cookie-Hinweis](#16-spenden-und-cookie-hinweis)
17. [Fehler, Rücknahme und Hilfe](#17-fehler-rücknahme-und-hilfe)
18. [Aufnahmeplan für Screenshots](#18-aufnahmeplan-für-screenshots)
19. [Vor der Freigabe und Quellen](#19-vor-der-freigabe-und-quellen)

## 1. Anmelden, Profil und Sicherheit

**Redaktion.** Die Administration teilt die richtige Anmeldeadresse und den persönlichen Zugang über den vereinbarten sicheren Weg mit.

1. Die bekannte Website über HTTPS öffnen und `/wp-admin/` aufrufen. Bei einem abweichenden Anmeldeweg die von der Administration bestätigte Adresse verwenden.
2. Benutzername und Passwort eingeben. Ein gegebenenfalls eingerichteter zweiter Faktor gehört zur Installation; das Theme richtet ihn nicht ein.
3. Oben rechts den Kontonamen und im Dashboard den richtigen Website-Namen prüfen.
4. Über **Profil** beziehungsweise den Kontonamen das eigene Profil öffnen. Den öffentlich angezeigten Namen und die hinterlegte E-Mail-Adresse prüfen; eine Änderung der E-Mail-Adresse kann eine Bestätigung erfordern.
5. Unter der Passwortfunktion ein langes, einmaliges Passwort erzeugen und im freigegebenen Passwortmanager speichern. Niemals Zugangsdaten in Beiträge, Personenfelder oder öffentliche Kontaktfelder schreiben.
6. Nach der Arbeit auf gemeinsam genutzten Geräten abmelden. Zugangsdaten nicht an die nächste Amtsinhaberin weiterreichen; den Wechsel über die Administration organisieren.

Bei vergessenem Passwort die Funktion **Passwort vergessen?** auf der Anmeldung nutzen. Kommt die Nachricht nicht an, Spamordner prüfen und die Administration kontaktieren. Wiederholte Anmeldeversuche oder zusätzliche Konten lösen ein Zustellproblem nicht.

**Begriffe:** Das *Backend* ist der angemeldete Verwaltungsbereich. Das *Frontend* ist die öffentliche Website. Ein *Entwurf* ist gespeichert, aber noch nicht öffentlich. Ein *Slug* ist ein kurzer URL-Name wie `ov-beispielort`. Eine *Zuordnung* legt den redaktionellen Bereich KV oder einen bestimmten OV fest; eine *Kategorie* beschreibt dagegen das Thema.

## 2. Rollen und Zuständigkeiten

Die folgende Matrix beschreibt die Theme-Rollen nach der Aktualisierung auf 0.7.0. Zusätzliche Plugins, manuelle Rechteänderungen und WordPress-Sonderfälle können die konkrete Oberfläche beeinflussen. „Alle“ meint hier die üblichen redaktionellen Inhalte, nicht die Verwaltung der gesamten WordPress-Installation.

| Aufgabe | Administration | KV-Autor | Geschäftsstelle: KV-Autor mit OV-Zugang | OV-Admin | OV-Autor |
|---|---|---|---|---|---|
| Bereich | KV und alle OVs | KV | KV und alle OVs | eigener OV | eigener OV |
| Beiträge anlegen, bearbeiten, veröffentlichen | alle | eigene im KV | alle Bereiche, auch fremde Autorenschaft | alle im eigenen OV | eigene im eigenen OV |
| Seiten anlegen, bearbeiten, veröffentlichen | alle | eigene im KV | alle Bereiche | alle im eigenen OV | keine Seitenrechte |
| Personen und Termine anlegen, bearbeiten, veröffentlichen | alle | eigene im KV | alle Bereiche | alle im eigenen OV | eigene im eigenen OV |
| Berechtigte Inhalte in den Papierkorb / wiederherstellen | ja | eigene im KV | auch Inhalte anderer Autoren | auch Inhalte anderer Autoren im eigenen OV | eigene im eigenen OV |
| Dauerhaftes Löschen von redaktionellen Inhalten und Medien | ja | nein | nein | nein | nein |
| Medien hochladen und zur Auswahl sehen | alle | eindeutig KV | alle Bereiche | eindeutig eigener OV | eindeutig eigener OV |
| Bestehende Kategorien/Abteilungen zuweisen | ja | beim eigenen Inhalt | bei berechtigtem Inhalt | bei berechtigtem Inhalt | bei berechtigtem Inhalt |
| Kategorien/Abteilungen neu verwalten | ja | regulär nein | regulär nein | regulär nein | regulär nein |
| OV anlegen, Name/Slug/Typ oder zugewiesene Startseite ändern | ja | nein | nein | nein | nein |
| OV-Kontakt, Header und OV-Einstellungen | alle | nein | kein regulärer Zugang | eigener OV | nein |
| Menüs und Menüplätze | alle | nein | nein | nein | nein |
| Globale Homepage, Spendenkonfiguration, KV-Stammdaten | Administration | nein | nein | nein | nein |
| Kreiskarte erzeugen, Medienmigration, Theme-Rollenübersicht | ja | nein | nein | nein | nein |
| Plugins, Updates, Backups, Benutzerverwaltung | Administration | nein | nein | nein | nein |

**OV-Admin ist kein WordPress-Administrator:** In 0.7.0 erhalten die eingeschränkten Theme-Rollen kein `edit_theme_options`; das Rollenupgrade entfernt dieses frühere Recht auch von bestehenden Theme-Rollen. Dadurch bleiben globale Einstellungen, Customizer und der WordPress-Menüeditor bei der Administration. Der OV-Admin kann weiterhin die angebotenen Einstellungen seines eigenen OVs unter **Verband** bearbeiten. Globale Menüs bearbeitet die Administration. Seitenvorlagen kann der OV-Admin auf seinen berechtigten Seiten selbst auswählen. Unter **Seiten → Vorlagen & Gestaltung** findet er eine Anleitung; im Blockeditor stehen unter **Vorlagen → Verband: Inhalte** drei anpassbare Inhaltsvorlagen bereit.

**Medienauswahl ist nicht gleich Bearbeitungsrecht:** OV-Autoren können Medien ihres Bereichs auch dann zur Auswahl sehen, wenn andere Personen sie hochgeladen haben. Das Bearbeiten fremder Medien kann dennoch an den engeren Autorenrechten scheitern. Die Geschäftsstelle kann bereichsübergreifend auswählen und bearbeiten.

**Bereich ist nicht Autorenschaft:** Die Zuordnung eines Beitrags oder Bilds bestimmt KV/OV. Der Name des ursprünglichen Uploaders ist kein verlässlicher Ersatz. Eingeschränkte KV-/OV-Konten bekommen keinen Zugriff auf unzugeordnete oder mehrfach zugeordnete Altbestände. Die Geschäftsstelle und die Administration können solche Bestände prüfen. Bei einem OV-Konto muss derzeit der Benutzername genau dem vorhandenen OV-Zuordnungsslug entsprechen; ohne gültigen Treffer bleibt der Bereich gesperrt.

**Keine Vertraulichkeitsfunktion:** Diese Grenzen betreffen die Redaktion. Veröffentlichte Inhalte und direkte öffentliche Medien-URLs bleiben öffentlich erreichbar. Die Mediathek ist kein geschütztes Dokumentenarchiv.

### Rollen prüfen oder ändern – Administration

1. **Benutzer → Theme-Rollen** öffnen.
2. Benutzername, Theme-Rolle und Zuständigkeit vergleichen. Die Übersicht enthält keine Passwörter und keine E-Mail-Spalte.
3. Nur ein anderes bestehendes Theme-Redaktionskonto auswählen und die benötigte Theme-Rolle setzen.
4. **Rolle speichern** betätigen und die Rückmeldung lesen. Bei abgelaufener Sicherheitsprüfung die Seite neu laden.
5. Danach mit einem passenden Testkonto Beiträge, Seiten, Termine und Medien prüfen.

Die Übersicht erstellt keine Konten, ändert nicht das eigene Konto oder Administratorkonten und vergibt keine Administratorrolle. Für eine OV-Rolle ist eine gültige OV-Zuordnung über den Benutzernamen erforderlich. Für bereichsübergreifende Redaktionsaufgaben ist `gk_kvautor_ov` vorgesehen. Neue Konten und abweichende Zuordnungskonzepte klärt die Administration separat.

## 3. Dashboard und Navigation

**Redaktion.** Nach der Anmeldung beginnt die Arbeit im Dashboard. Die linke Navigation enthält je nach Recht **Beiträge**, **Seiten**, **Personen**, **Termine**, **Medien** und **Profil**. Das Theme ordnet Inhaltslisten in Unterpunkte **Kreisverband** und die vorhandenen OV-Namen. Ein sichtbarer Unterpunkt verleiht noch kein zusätzliches Recht; beispielsweise bekommt ein reiner KV-Autor dadurch keinen OV-Zugriff.

1. Zuerst den Inhaltstyp und den gewünschten Bereich wählen.
2. In der Liste nach Titel suchen oder vorhandene Filter einsetzen. Bei scheinbar fehlenden Inhalten zusätzlich Statusfilter wie Entwürfe und Papierkorb prüfen.
3. Über **Erstellen**, **Hinzufügen** beziehungsweise **Neuer Termin** oben in der Liste einen Inhalt anlegen. Die Beschriftung hängt von Inhaltstyp und WordPress-Version ab.
4. Im Editor die Box **Zuordnung (KV/OV)** kontrollieren. Ein Listenfilter allein ist keine Bestätigung der gespeicherten Zuordnung.
5. Bei einem OV-Konto zeigt das Dashboard gegebenenfalls **Dein Ortsverband** mit dem zuständigen Bereich an.

**Verband**, **Menüs**, **KV-Setup**, die Benutzerverwaltung und technische Werkzeuge sind kein regulärer Arbeitsweg für die Redaktion. Ein fehlender Menüpunkt wird nicht durch eine Administratorrolle für die Geschäftsstelle behoben. Kommentare sind im Theme aus der Navigation entfernt; dieses Handbuch setzt keine Kommentarverwaltung voraus.

## 4. Beiträge: Entwurf, Veröffentlichung und Papierkorb

### Einen Beitrag erstellen – Redaktion

1. **Beiträge → Kreisverband** oder den passenden OV öffnen und einen neuen Beitrag anlegen.
2. Einen aussagekräftigen Titel und den Text eingeben. Im Blockeditor gliedern Überschriften, Absätze, Listen und Bilder den Inhalt. Den Seitentitel nicht nochmals als größte Überschrift wiederholen.
3. **Zuordnung (KV/OV)** auf genau den beabsichtigten Bereich setzen. Die Geschäftsstelle kontrolliert dies ausdrücklich, weil ihr Konto alle Bereiche bearbeiten darf.
4. Eine vorhandene Kategorie und bei Bedarf Schlagwörter auswählen. Eine fehlende Kategorie von der Administration anlegen lassen.
5. Ein Beitragsbild auswählen und, soweit im Editor vorhanden, einen kurzen **Textauszug** schreiben. Bildrechte und öffentliche Freigabe prüfen.
6. **Entwurf speichern** wählen. Anschließend die **Vorschau** öffnen und Text, Links, Bildausschnitt, Mobilansicht und Zuordnung prüfen.
7. Erst nach redaktioneller Freigabe **Veröffentlichen** bestätigen. Ein späteres Datum kann eine geplante Veröffentlichung auslösen; deren tatsächliche Ausführung hängt von WordPress und dem Hosting ab.
8. Die öffentliche Adresse in einem abgemeldeten Fenster öffnen. Danach auch die passende KV-/OV-Liste prüfen.

Die Theme-Rollen dürfen im beschriebenen Umfang selbst veröffentlichen. Eine automatische Freigabe durch eine zweite Person ist nicht eingebaut; eine vereinbarte Gegenlese muss die Geschäftsstelle organisieren.

### Einen veröffentlichten Beitrag korrigieren

1. Den vorhandenen Beitrag öffnen; keinen zweiten Beitrag als Ersatz anlegen.
2. Die nötige Stelle korrigieren und **Aktualisieren** wählen.
3. Die öffentliche Ausgabe neu laden. Bei veraltetem Inhalt Browser- und gegebenenfalls Website-Cache durch die Administration prüfen lassen.
4. Bei einem falschen URL-Namen zuerst die Administration fragen: Eine nachträgliche Änderung kann bestehende Links betreffen.

### Zurücknehmen und wiederherstellen

1. Soll der Inhalt vorübergehend nicht öffentlich sein, im Editor den Status auf **Entwurf** zurücksetzen und speichern.
2. Soll er aus dem redaktionellen Bestand entfernt werden, in der Übersicht **Papierkorb** wählen. Vorher Links, Startseiten-Verweise und die betroffenen OV-Seiten bedenken.
3. Für eine Rücknahme in der Inhaltsliste den Filter **Papierkorb** öffnen und **Wiederherstellen** wählen.
4. Den wiederhergestellten Inhalt als Entwurf kontrollieren: Text, Zuordnung, Bild und Veröffentlichungsstatus. Nicht von einer automatisch wieder öffentlichen Seite ausgehen.
5. Bei Bedarf erneut veröffentlichen und die öffentliche Adresse testen.

**Dauerhaft löschen** ist für die vier eingeschränkten Theme-Rollen gesperrt. Der Papierkorb ist trotzdem kein Backup: Die WordPress-Aufbewahrungsfrist und automatische Bereinigung gelten weiterhin. Wie lange Inhalte wiederherstellbar sind, teilt die Administration mit; bei deaktiviertem Papierkorb ist gegebenenfalls keine sichere Entfernung verfügbar.

Revisionen können frühere Textstände wiederherstellen, ersetzen aber weder ein Datenbankbackup noch eine vollständige Sicherung aller Theme-Zusatzfelder. Vor größeren Änderungen die bisherige Fassung und Einstellungen dokumentieren.

## 5. Seiten und Menüs

### Eine Seite bearbeiten oder anlegen – Redaktion

Seiten enthalten länger gültige Informationen, etwa Kontakt, Mitmachen und OV-Vorstellung. Eine Seite kann anderen Seiten untergeordnet sein; diese Hierarchie ist etwas anderes als ein Navigationsmenü.

1. Unter **Seiten** den Bereich und die vorhandene Seite auswählen oder eine neue Seite anlegen.
2. Titel, Inhalt und **Zuordnung (KV/OV)** prüfen. Eine neue OV-Unterseite dem richtigen OV zuordnen.
3. Falls benötigt, unter den Seiteneigenschaften eine übergeordnete Seite aus demselben Bereich wählen. Bestehende Hierarchien nicht beiläufig verändern.
4. Entwurf speichern und Vorschau öffnen. Eine OV-Unterseite kann ihr Layout durch die übergeordnete OV-Seite erhalten.
5. Veröffentlichen beziehungsweise aktualisieren. Für die Aufnahme ins Menü der Administration Seiten-URL, gewünschte Beschriftung und Position mitteilen.

### Seitenvorlagen und Inhaltsvorlagen verwenden

Eine *Seitenvorlage* beziehungsweise ein *Template* bestimmt das Layout. Auf einer bearbeitbaren Seite lässt sie sich rechts in den Seiteneinstellungen unter **Template** auswählen. OV-Admins können so **OV-Startseite** und **OV-Unterseite** verwenden. Vor dem Veröffentlichen die Vorschau prüfen.

*Inhaltsvorlagen* fügen bearbeitbare Blöcke ein: Über das Plus-Zeichen im Editor unter **Vorlagen → Verband: Inhalte** stehen **Vorstellung mit Bild und Text**, **Unsere Themen** und **Mitmachen und Kontakt** bereit. Texte und Bilder anschließend durch eigene Inhalte ersetzen. Das verleiht keine Rechte an globalem Design oder fremden Seiten.

| Vorlage im Theme | Verwendung |
|---|---|
| Standard | normale Inhaltsseite |
| Startseite | KV-Homepage mit den konfigurierten Startseitenbereichen |
| Einspaltig | Inhaltsseite ohne die übliche Mehrspaltenaufteilung |
| Artikel-Archiv | Beitragsübersicht |
| Landingpage | besondere Einstiegsseite, gegebenenfalls mit Kategorienbox |
| Story | besondere Textdarstellung mit zusätzlichem Inhaltsfeld |
| OV-Startseite | lokale Ortsverbands-Startseite |
| OV-Unterseite | Inhalt im OV-Layout |
| Mach mit – Newsletteranfrage | Seiteninhalt mit Newsletter-Anfrageformular |
| Kitchen Sink | Demonstration der Gestaltungselemente; keine normale öffentliche Inhaltsvorlage |

Die zentrale Seite mit dem Slug `termine` erhält über `page-termine.php` eine besondere Terminansicht. „Termine“ ist deshalb kein zusätzliches auswählbares Theme-Template. Ein Vorlagenwechsel allein macht eine Seite weder zur WordPress-Startseite noch zur zugewiesenen OV-Homepage.

### Menüs – Administration

1. **Menüs → Kreisverband** oder den betreffenden OV öffnen.
2. Das zugeordnete **Hauptmenü** beziehungsweise **Footer**-Menü prüfen und über den angebotenen Link den WordPress-Menüeditor öffnen.
3. Die veröffentlichte Seite oder einen geprüften individuellen Link hinzufügen. Beschriftung und Reihenfolge festlegen; eine Unterebene bewusst verwenden.
4. Menü speichern und in der Theme-Menüseite dem richtigen Hauptmenü-/Footer-Platz zuweisen. Ein gespeichertes Menü ist ohne Platzzuweisung nicht automatisch sichtbar.
5. Hauptnavigation, mobile Navigation und Footer im betreffenden KV-/OV-Kontext testen. Auch Datenschutz und Impressum über ihre tatsächlichen Links öffnen.

Jede Zuordnung hat eigene Menüplätze. Gemeinsam verwendete Menüobjekte können mehrere Stellen beeinflussen; die Administration sollte für unterschiedliche Bereiche getrennte Menüs verwenden. Die Geschäftsstelle kann Seiten ändern, erhält dadurch aber keine Menürechte.

## 6. Kreisverband, Ortsverbände und externe Websites

**Redaktion:** Beiträge, Seiten, Personen und Termine im passenden Bereich pflegen und Änderungswünsche melden. **Administration:** KV-Stammdaten, OV-Anlage, Typ, Name, Slug und Homepage-Zuweisung. **OV-Admin:** die angebotenen Kontakt- und Darstellungsfelder des eigenen OVs.

Die KV-Stammdaten liegen unter **KV-Setup**: Name, Kurzname, Anschrift, öffentliche E-Mail, Telefon, Website, Social-Media-Angaben, Verbandslinks und Pflichtseiten. Dieser Einrichtungsweg legt keine E-Mail-Postfächer an. Ein ausgefülltes Setup ist auch keine inhaltliche Prüfung der Rechtstexte.

### OV einrichten – Administration

1. Unter **Verband** in der Liste **Ortsverbaende** den Eintrag bearbeiten oder **Neuen Ortsverband hinzufügen** wählen.
2. Name und Slug sorgfältig festlegen. Der Slug wird für Zuordnung, URLs und OV-Kontozuständigkeit verwendet; Änderungen sind ein geplanter Administrationsvorgang.
3. Typ wählen: **Ortsverband** (`ov`), **Ortsgruppe** (`ortsgruppe`) oder **Werbeseite** (`werbung`, Einladung zur Gründung).
4. Bei lokaler Website eine passende veröffentlichte Seite als **Startseite** zuweisen. Bei externer Website unter **Kontaktdaten → Website** die vollständige HTTP-/HTTPS-Adresse eintragen und aufrufen.
5. Öffentliche Kontakt-E-Mail, Telefon, Anschrift und freigegebene Social-Media-Profile ergänzen. Zugangsdaten gehören in keines dieser Felder.
6. Speichern; anschließend gegebenenfalls Impressum und Datenschutz nach Abschnitt 10 auswählen.
7. Die öffentliche OV-Liste und die Kreiskarte prüfen. Ein ausdrücklich konfiguriertes externes Kartenziel gilt für beide.

### Welches Linkziel erscheint?

Für `[ortsverband_liste]` gilt:

| Vorhandene Konfiguration | Ausgabe |
|---|---|
| Kartentyp **Externer Link** mit gültiger Website | Link genau zu dieser Website |
| Kartentyp **Ortsverband** oder **Ortsgruppe**, veröffentlichte lokale Homepage | Link zur lokalen Homepage |
| Kartentyp **Ortsverband** oder **Ortsgruppe**, keine lokale Homepage, gültige OV-Website | Link zur OV-Website aus dem Kontaktfeld |
| Kartentyp **Ortsverband** oder **Ortsgruppe**, kein nutzbares Ziel | **Im Aufbau**, ohne Link |
| Kartentyp **Kontaktseite** | Link zur Informationsseite mit Kontaktmöglichkeit |
| Kartentyp **Ohne Link** | **Im Aufbau**, ohne Link |

Eine nur als Entwurf vorhandene Homepage hat keinen Vorrang vor einer gültigen externen Website. Der Altwert `full` wird als `ov` behandelt. Das OV-Verzeichnis öffnet seine Links im selben Fenster. Auch die Namenslinks in `[gliederungen]` und `[arbeitsgemeinschaften]` verwenden dieselbe Auflösung. Eine veröffentlichte lokale Homepage wird auch bei alten Typangaben wie `werbung` erkannt; ohne solche Homepage bleibt `werbung` im Aufbau und bietet eine Informationsseite. Zusätzliche Kontakt-Website-Symbole dieser älteren Ansichten separat prüfen.

Vorhandene veröffentlichte Seiten unter dem OV-Slug, beispielsweise `/ov-beispielort/`, werden auch ohne ältere Startseiten-Zuweisung erkannt, wenn sie diesem OV zugeordnet sind. Eine unzugeordnete Altseite muss dafür die Vorlage **OV-Startseite** besitzen. Die ausdrückliche Startseiten-Auswahl bleibt der übersichtlichste Einrichtungsweg; eine fremd zugeordnete Seite wird nicht anhand ihres Namens übernommen.

Karte und mobile Gemeindeliste verwenden dieselbe gespeicherte Aktion. Die Spalte **Klickziel auf der Website** im Karteneditor zeigt die derzeit öffentliche Zieladresse oder **Im Aufbau**. Eine Änderung am Typ oder Ziel ist erst nach **Speichern** öffentlich. **Kontaktseite** ist eine ausdrückliche Wahl; die Seite zeigt den OV-Kontakt oder ersatzweise den Kontakt der Geschäftsstelle. Diese Kontaktdaten müssen in der Installation gepflegt sein. Leere Ziele, `#` und Platzhalter wie `example.com` werden nicht als Website übernommen. Auf kleinen Bildschirmen öffnet ein Antippen der Karte zunächst die Gemeindeansicht; deren Button führt zum OV. Mit Escape lässt sich diese Ansicht schließen, der Fokus kehrt zum Ausgangspunkt zurück.

### Gemeinde anklicken funktioniert nicht, direkte OV-Adresse schon

Eine funktionierende direkte Adresse wie `/ov-beispielort/` beweist noch nicht, dass Karte, Gemeindeliste oder Menü auf diese Adresse zeigen. Die Geschäftsstelle kann das mit folgender Prüfung eingrenzen; die Administration korrigiert anschließend die Konfiguration.

1. In der öffentlichen OV-Liste die Gemeinde suchen, den Link öffnen und die tatsächlich erreichte URL notieren. Bei fehlendem Ziel keine vermeintliche Homepage erfinden.
2. Dieselbe Gemeinde in der Desktop-Kreiskarte auswählen. Stimmen Ziel und sichtbarer OV-Name mit der direkten Adresse überein?
3. Die Seite in schmaler Mobilansicht öffnen, in der suchbaren OV-Liste dieselbe Gemeinde auswählen und anschließend über **Karte anzeigen** auch die Karte prüfen. Falls sich eine Detailansicht öffnet, deren weiterführenden Link ebenfalls testen.
4. Mit Tabulatortaste und Eingabetaste die angebotenen Links bedienen. Prüfen, ob der Fokus sichtbar bleibt und die richtige Gemeinde erreichbar ist.
5. Die Administration vergleicht unter **Verband → OV bearbeiten** die lokale Startseite und externe Kontakt-Website sowie unter **Verband → Kreiskarte → Gemeinden konfigurieren** die Verknüpfung. Gemeinde-Slug und OV-Slug müssen nicht identisch heißen; entscheidend ist die passende Zuordnung in der Karte.
6. Falls kein nutzbares Ziel existiert, einen ehrlichen Aufbauzustand vorsehen. Bei einer als aktiv dargestellten Gemeinde ohne Funktion oder einem Scheinlink wie `#` den Fehler mit Gemeinde und Oberfläche melden. „Im Aufbau“ darf keine funktionsfähige lokale Website vortäuschen.
7. Nach Korrektur **jede konfigurierte Gemeinde** erneut über Liste, Karte, Mobilansicht und Tastatur prüfen. Pro Gemeinde Quelle, erwartete URL, erreichte URL, Seiten-/Fehlerstatus und sichtbaren OV-Kontext festhalten. Den technischen HTTP-Status kann die Administration im Browser-Netzwerkprotokoll kontrollieren.
8. Die Prüfliste erst nach erfolgreichem Klicktest als erledigt markieren; eine Prüfung ausschließlich direkt eingegebener OV-Adressen genügt nicht.

**Kein OV-Löschen als Reparatur:** Die Löschfunktion eines OV kann zugeordnete Inhalte in den Papierkorb verschieben und die Zuordnung selbst entfernen. Bei einer Umbenennung, Pause oder einem falschen Link deshalb den Datensatz korrigieren lassen; vorher Backup und Auswirkungen prüfen.

## 7. Termine und iCal

### Einen Termin erstellen – Redaktion und berechtigte OV-Nutzer

1. **Termine → passender Bereich → Neuer Termin** öffnen. Die Zuordnung zum ausgewählten Verband wird übernommen. **Alle Termine** zeigt der berechtigten Redaktion die bereichsübergreifende Liste.
2. Einen Titel und eine Beschreibung eingeben: Was findet statt, für wen, mit welcher Anmeldung und gegebenenfalls welcher Barrierefreiheit?
3. Unter **Termin-Details** das **Startdatum** eintragen. Im Blockeditor erscheinen die Felder in der Seitenleiste, im klassischen Editor in einer eigenen Box.
4. **Startzeit** sowie **Enddatum** und **Endzeit** eintragen, soweit bekannt. Das Ende darf nicht vor dem Beginn liegen. Für eine Abendveranstaltung beispielsweise denselben Tag und `19:00` bis `21:00` wählen.
5. Bei **Ganztägig** den Haken setzen und das letzte Veranstaltungstagsdatum als Enddatum verwenden. Es sind dann keine Uhrzeiten nötig.
6. **Ort** als verständlichen Namen, **Adresse** mit Straße und Ort, **Veranstalter** sowie gegebenenfalls einen vollständigen externen **Link** angeben.
7. Eine vorhandene **Termin-Kategorie**, ein passendes **Beitragsbild** und genau eine **Zuordnung (KV/OV)** wählen. Neue Kategorien legt die Administration an.
8. Entwurf speichern und Vorschau prüfen: Datum, Uhrzeit, Bild, Link, Ort und OV-Kontext.
9. Veröffentlichen und die öffentliche Terminseite sowie die passende Liste kontrollieren.
10. Für Korrekturen den bestehenden Termin bearbeiten und aktualisieren. Nicht als neuen Termin duplizieren, wenn nur Zeit oder Ort falsch sind.

Termine verwenden die in WordPress eingestellte Zeitzone. Bei Zeitverschiebungen die Administration die Website-Zeitzone prüfen lassen; die Geschäftsstelle soll Uhrzeiten nicht durch willkürliche Zuschläge „reparieren“. Das Theme bietet keine bedienbare Serienverwaltung: Wiederkehrende Sitzungen als einzelne Termine mit jeweils geprüftem Datum anlegen.

Für eine Absage können Titel und Beschreibung zunächst deutlich **Abgesagt** kennzeichnen. Eine automatische Absage-Nachricht an Kalenderabonnenten oder Teilnehmer wird dadurch nicht versandt. Soll der Termin verschwinden, ihn nach Abschnitt 4 zurücknehmen oder in den Papierkorb legen; eine Löschung entfernt bereits importierte Kalenderkopien nicht zuverlässig.

### Termine ohne lokale OV-Unterseite

Eine externe Website oder eine Ortsgruppe kann ebenfalls Termine im gemeinsamen Kalender unter **/termine** und im iCal-Export haben. Dafür genügt eine interne Zuordnung; eine lokale OV-Unterseite ist nicht erforderlich.

1. Als Administration **Verband → Kreiskarte → Gemeinden konfigurieren** öffnen.
2. Bei der Gemeinde **Im Terminmenü anzeigen** aktivieren. Falls mehrere Gemeinden denselben Verband nutzen, den bestehenden Verband auswählen.
3. **Speichern**. Bei Bedarf wird eine interne Zuordnung **Nur Termine** angelegt. Sie erscheint ausschließlich in der Terminverwaltung, nicht als eigener Bereich für Seiten, Beiträge, Personen, Medien, Menüs oder in der allgemeinen OV-Verwaltung. Kartenaktion und externe Zieladresse bleiben bestehen.
4. Über **Termine verwalten** oder **Termine → gewünschter Verband** einen Termin anlegen. Vor der Veröffentlichung die Zuordnung prüfen, danach den gemeinsamen Kalender und den passenden Filter kontrollieren.

Abwählen blendet den Eintrag im gemeinsamen Terminmenü aus. Vorhandene Termine, Zuordnungen und ihre öffentliche Anzeige bleiben erhalten. OV-Konten behalten ihren eigenen Bereich; die Einstellung erweitert keine Benutzerrechte. Bei gemeinsam genutzten Zuordnungen bleibt der Menüeintrag sichtbar, solange mindestens eine Gemeinde ihn aktiviert hat.

Der Karteneditor kennzeichnet reine Terminzuordnungen mit **Nur Termine**. Unter **Verwendung ändern** kann die Administration die Kennzeichnung ausdrücklich ändern. Ein bestehender vollständiger OV wird durch den Termin-Haken nicht umgestellt. Für eine Umstellung auf **Nur Termine** dürfen keine lokale Startseite oder anderen zugeordneten Inhalte vorhanden sein; Termine bleiben bestehen. Das ausdrückliche Anlegen einer lokalen Unterseite im Karteneditor erweitert eine reine Terminzuordnung zum vollständigen OV-Bereich.

### Listen richtig eingrenzen

Auf einer OV-Seite übernimmt die Terminausgabe den OV-Kontext und darf nicht durch ein fremdes Shortcode-Attribut auf einen anderen OV umgelenkt werden. Die zentrale ungefilterte KV-Terminübersicht zeigt bewusst Termine aus allen Bereichen. Nur-KV-Ausgaben verwenden `zuordnung="kreisverband"`; für einen einzelnen OV dessen echten Slug verwenden.

```text
[termine anzahl="10" zuordnung="ov-beispielort"]
[naechste_termine anzahl="5" zuordnung="kreisverband"]
```

`ov-beispielort` ist ein Beispiel und muss durch einen vorhandenen Slug ersetzt werden. Die Listen zeigen regulär Termine mit Startdatum ab heute. Ein mehrtägiger Termin, der bereits gestern begonnen hat, kann deshalb aus der Liste der kommenden Termine herausfallen. `vergangen="ja"` beim Shortcode `[termine]` hebt derzeit den Zukunftsfilter auf und sortiert absteigend; es bedeutet **alle Termine einschließlich zukünftiger**, keine reine Vergangenheitsliste.

### Kalenderdatei und Abonnement

1. Einen veröffentlichten Termin öffnen und den angebotenen iCal-Link verwenden. Technisch ist dies die Terminadresse mit `?ical=1`.
2. Die heruntergeladene `.ics`-Datei im gewünschten Kalender öffnen und Titel, Datum, Uhrzeit und Ort prüfen. Ein Import erzeugt üblicherweise eine lokale Kopie.
3. Für regelmäßige Aktualisierungen in der Kalenderanwendung ein **Kalenderabonnement per URL** einrichten und den passenden Feed verwenden.
4. Nach einer Änderung den Kalender aktualisieren und berücksichtigen, dass die Abrufintervalle von der Kalenderanwendung abhängen.

| Feed-Pfad auf der eigenen Website | Inhalt |
|---|---|
| `/termine/ical/` | veröffentlichte Termine aller Bereiche |
| `/termine/ical/?zuordnung=kreisverband` | nur KV |
| `/termine/ical/?zuordnung=ov-beispielort` | nur dieser OV |

Das Theme exportiert bis zu 200 Termine mit Startdatum ab drei Monaten vor dem aktuellen Datum. Dies ist kein vollständiges Langzeitarchiv. Ganztagstermine werden mit dem technisch exklusiven Folgetag als iCal-Ende exportiert; die Geschäftsstelle trägt im Editor weiterhin den letzten Veranstaltungstag ein. Uhrzeiten werden für den Export aus der Website-Zeitzone nach UTC umgerechnet. Ein Kalenderimport, eine Benachrichtigung und ein Kalenderabonnement sind drei verschiedene Vorgänge.

## 8. Personen, Abteilungen und Zuordnungen

Eine Person ist ein eigener öffentlicher Inhalt. Eine *Abteilung* gruppiert Personen, etwa Vorstand, Gemeinderat oder Kandidierendenliste. Eine Person kann mehrere Abteilungen haben, gehört redaktionell aber zu genau einem KV-/OV-Bereich.

### Person pflegen – Redaktion

1. **Personen → Bereich** öffnen, die Person suchen und erst bei fehlendem Eintrag neu anlegen.
2. Den Namen als Titel, eine freigegebene Beschreibung und ein geeignetes Portrait als Beitragsbild eintragen.
3. Unter **Kontaktdaten** nur zur Veröffentlichung freigegebene Website, E-Mail, Telefon, Anschrift und Social-Media-Angaben pflegen. Private Erreichbarkeit nicht ungefragt veröffentlichen.
4. **Zuordnung (KV/OV)** und in der Seitenleiste die vorhandenen **Abteilungen** wählen.
5. Speichern beziehungsweise neu laden. Die Box **Abteilungen** zeigt anschließend für jede zugewiesene Abteilung **Position**, **Funktion** und **Nr. verstecken**.
6. Position numerisch eintragen und Funktion verständlich benennen. Personen mit Position stehen zuerst, aufsteigend; ohne Position folgen sie alphabetisch.
7. **Nr. verstecken** blendet ausschließlich die Positionsnummer aus. Die Person bleibt sichtbar und behält ihre Sortierung.
8. Vorschau und anschließend alle betroffenen Listen prüfen, dann veröffentlichen beziehungsweise aktualisieren.

Bei einem Amtswechsel eine alte Abteilungszugehörigkeit und Funktionsbezeichnung gezielt entfernen oder ändern; eine Person muss nicht allein deshalb gelöscht werden. Ob ein historisches Profil weiter öffentlich bleiben soll, wird redaktionell und mit den Verantwortlichen für Datenschutz entschieden.

Neue Abteilungen oder globale Umbenennungen übernimmt die Administration. Ein Abteilungs-Slug kann in Shortcodes stecken und darf nicht ohne Prüfung geändert werden. Falls die Begriffsverwaltung wegen der nach Bereichen umgebauten Seitenleiste nicht sichtbar ist, soll die Administration den WordPress-Bildschirm der betreffenden Taxonomie öffnen; die Geschäftsstelle braucht dafür kein zusätzliches Menü.

**Listen darstellen:** Für neue Inhalte den Block **Abteilung** oder `[abteilung slug="…"]` verwenden. Der Block begrenzt auf OV-Seiten automatisch auf den Seitenbereich. Ein allein stehender Shortcode ohne `zuordnung` kann Personen aus mehreren Bereichen mit Filterreitern zeigen; bei einer OV-Liste deshalb den OV-Slug ausdrücklich setzen.

## 9. Medien, Beitragsbilder und Altbestand

### Ein neues Bild verwenden – Redaktion

1. Vor dem Upload Bildrechte, Freigaben abgebildeter Personen und gewünschte öffentliche Nutzung klären. Ein sinnvoller Dateiname erleichtert die spätere Suche.
2. Möglichst den bereits korrekt zugeordneten Beitrag, Termin oder die Seite öffnen und dort hochladen. Ein Upload mit eindeutig zugeordnetem Elterninhalt kann dessen Bereich übernehmen.
3. Unter **Medien** beziehungsweise in den Mediendetails **Zuordnung (KV/OV)** kontrollieren. Bei einem KV-Autorenkonto mit OV-Zugang wird ein unabhängiger neuer Upload sonst grundsätzlich dem KV zugeordnet; eine geöffnete OV-Liste allein ist keine ausreichende Garantie.
4. Einen **Alternativtext** eintragen, der den relevanten Bildinhalt erklärt. Bei rein dekorativen Bildern den Alternativtext bewusst leer lassen. Bildnachweis und Bildunterschrift nach redaktioneller Vorgabe pflegen.
5. Als **Beitragsbild** auswählen oder an der gewünschten Textstelle einfügen. Das Beitragsbild dient oft der Übersicht und der Vorschau in sozialen Netzwerken; es ist nicht dasselbe wie ein Bild mitten im Text.
6. Speichern und die öffentliche Darstellung auf großem und kleinem Bildschirm prüfen.

OV-Konten erhalten bei neuen Uploads ihren eigenen Bereich. Ein reiner KV-Autor sieht eindeutig zugeordnete KV-Medien. Die Geschäftsstelle sieht bereichsübergreifend alle Medien. Bei eingeschränkten Konten ist eine neue Auswahl eines Bilds aus einem fremden Bereich gesperrt. Bereits gespeicherte Beitragsbild-Verweise werden durch die neue Auswahlbegrenzung nicht automatisch ersetzt oder gelöscht.

### Warum ein älteres Bild fehlt

Frühere Medienauswahl orientierte sich unter anderem am Autor; fehlende Zuordnungen konnten zudem pauschal beim KV landen. Der aktuelle Bereichsfilter arbeitet mit der gespeicherten `gk_zuordnung`. Ein Bild kann deshalb noch öffentlich in einem Beitrag erscheinen, obwohl es einem OV-Konto nicht mehr zur neuen Auswahl angeboten wird.

1. Filter, Suchtext, Bereich und Papierkorb prüfen.
2. Die Bild-ID, die bisherige Verwendung und die aktuelle Zuordnung durch die Geschäftsstelle oder die Administration prüfen lassen.
3. Ein unzugeordnetes, mehrfach zugeordnetes oder widersprüchliches Bild nicht als „verloren“ behandeln und nicht vorschnell neu hochladen.
4. Für gemeinsame Verwendung mit mehreren OVs eine redaktionelle Entscheidung treffen. Die Auswahl folgt genau einem Bereich; es gibt keine allgemeine Freigabegruppe „alle OVs“ für eingeschränkte Konten.

### Altbestand sicher zuordnen – Administration

Die neue Funktion **Medien → Zuordnung prüfen** ist eine Vorschau mit einzeln bestätigten Änderungen. Sie verschiebt keine Dateien und ersetzt keine Bild-IDs.

1. Datenbank und Dateien sichern, Wiederherstellung testen und die Prüfung zunächst in einer Testkopie durchführen.
2. **Medien → Zuordnung prüfen** öffnen. Pro Seite werden bis zu 50 Medien mit ID/Titel, aktueller Zuordnung und Prüfaktion angezeigt; weitere Seiten ebenfalls prüfen.
3. Den Vorschlag mit der tatsächlichen Verwendung vergleichen. Er beruht auf Elterninhalt oder OV-Benutzername und ist kein Nachweis, dass das Bild ausschließlich dort genutzt wird.
4. Bei **Widersprüchliche Hinweise – manuell prüfen** oder gemeinsam verwendeten Bildern zunächst die verwendenden Inhalte ermitteln. Es erfolgt keine automatische Auswahl des Vorschlags.
5. Die geprüfte Zielzuordnung wählen und für genau dieses Medium **Geprüfte Zuordnung übernehmen** betätigen.
6. Medienauswahl mit KV-, zwei unterschiedlichen OV-Konten und der Geschäftsstelle prüfen. Vorhandene Beitragsbilder und öffentliche URLs müssen weiter funktionieren.
7. Bei einer falschen Änderung **Letzte Zuordnung rückgängig machen** verwenden. Die vorherigen Zuordnungen werden aus dem gespeicherten Rücknahmestand wiederhergestellt.
8. Wenn sich die Zuordnung seitdem geändert hat oder eine ursprüngliche Zuordnung entfernt wurde, stoppt die Rücknahme mit einem Konflikt. Dann manuell prüfen; nicht durch weitere Massenänderungen übergehen.

Pro Medium steht ein Rücknahmestand dieses Prüfverfahrens zur Verfügung, keine unbegrenzte Versionsgeschichte. Eine vorhandene Rücknahme blockiert eine weitere Übernahme in dieser Ansicht, bis sie geklärt ist. Manuelle Änderungen außerhalb dieser Prüffunktion besitzen nicht automatisch denselben Rücknahmeweg. Die Prüfung der Zuordnung behebt außerdem keine fehlenden Dateien oder defekten Bildgrößen; diese Fälle benötigt die Administration separat.

### Medien entfernen

1. Vorher alle bekannten Verwendungen prüfen. Das Entfernen eines Beitragsbilds aus einem Beitrag löscht nicht die Mediendatei.
2. Für eine berechtigte Entfernung die **Listenansicht** der Mediathek verwenden und **Papierkorb** wählen.
3. Bei Bedarf im Hinweis **Medien-Papierkorb öffnen** und dort **Wiederherstellen** wählen; anschließend Mediathek und Verwendungen kontrollieren.

Die eingeschränkten Rollen dürfen auch Medien nicht endgültig löschen. Bei fehlender Papierkorbfunktion die Administration einschalten. Aufbewahrungsfrist und Dateibackup bleiben relevant; eine Datei kann in mehreren Inhalten verwendet werden.

## 10. Datenschutz und Impressum

**Redaktion:** freigegebene Texte auf berechtigten Seiten bearbeiten und Links prüfen. **Administration beziehungsweise eigener OV-Admin:** die passenden Pflichtseiten auswählen. Inhaltliche Rechtsprüfung erfolgt durch die zuständige Stelle, nicht durch das Theme.

### KV-Seiten festlegen – Administration

1. Aktuelle Impressums- und Datenschutztexte als Seiten erstellen oder vorhandene Seiten prüfen.
2. Beide Seiten veröffentlichen und ohne Anmeldung sowie ohne Passwort erreichbar machen.
3. In **KV-Setup → Pflichtseiten** die richtigen Seiten auswählen und speichern.
4. Bei Bedarf auch die WordPress-Datenschutzseite unter **Einstellungen → Datenschutz** konsistent auswählen.
5. Footer, Cookie-Hinweis und Anfrageformular abgemeldet prüfen. Nicht nur die Beschriftung, sondern das tatsächliche Linkziel kontrollieren.

Automatisch vorgefüllte oder historische Mustertexte sind keine fertige Erklärung für die aktuelle Installation. Namen, Anschrift, Verantwortlichkeit und alle tatsächlich eingesetzten Dienste prüfen lassen. Das gilt besonders für aus früheren Installationen übernommene München-Verweise.

### Eigene OV-Seite oder KV-Seite übernehmen

1. Die für den OV freigegebene eigene Seite anlegen und veröffentlichen, falls eine eigene Erklärung gebraucht wird.
2. Unter **Verband → betreffenden OV bearbeiten → Impressum & Datenschutz** die Auswahl öffnen; beim OV-Admin erscheint nur der eigene OV.
3. Entweder die eigene veröffentlichte Seite wählen oder bewusst **KV-Impressum verwenden** beziehungsweise **KV-Datenschutz verwenden** setzen.
4. Speichern und den Footer auf der OV-Startseite sowie auf einer OV-Unterseite, einem Beitrag und einem Termin öffnen.
5. Zusätzlich etwaige manuell eingetragene Footer-/Menülinks und Links im Seitentext prüfen. Diese werden nicht allein durch eine geänderte Pflichtseiten-Auswahl korrigiert.

Die OV-Auswahl wird in `_gk_impressum_page` beziehungsweise `_gk_datenschutz_page` gespeichert. Ist die OV-Seite nicht nutzbar, verwendet die Ausgabe eine veröffentlichte, nicht passwortgeschützte KV-Seite. Fehlt auch diese, gibt es keine verlässliche Pflichtseiten-Verlinkung; die Administration muss die Einrichtung vervollständigen.

Das Anfrageformular verwendet die konfigurierte KV-Datenschutzseite mit Rückfall auf die WordPress-Datenschutzseite, nicht automatisch eine beliebige OV-Erklärung. Beim Einsatz des Formulars auf einer OV-Seite muss die verlinkte Erklärung die tatsächliche Übermittlung an die Geschäftsstelle abdecken.

## 11. Newsletter- und Kontaktanfragen

### Was das Newsletterformular macht

`[newsletter_anfrage]` übermittelt die angegebene E-Mail-Adresse und den Hinweis auf den Kontaktwunsch an die **fest im Theme konfigurierte Empfängeradresse**. Die Administration prüft diese Adresse vor dem Einsatz. Es legt **kein Abonnement**, kein WordPress-Benutzerkonto und keine Newsletterliste an. Es versendet auch keine automatische Bestätigungsnachricht an die interessierte Person.

Die Pflichtbestätigungen betreffen die Bearbeitung der Anfrage und die Kontaktaufnahme zur Newsletteraufnahme. Sie sind noch kein technisch verifizierter Nachweis einer Newsletter-Anmeldung. Die Website prüft die Schreibweise einer E-Mail-Adresse, nicht die Inhaberschaft des Postfachs.

### „Mach mit“ einrichten

**Variante A – Administration:** Die gewünschte Seite bearbeiten und als Vorlage **Mach mit – Newsletteranfrage** (`page-templates/mach-mit.php`) auswählen. Die Vorlage gibt den Seiteninhalt aus und ergänzt das Anfrageformular. Enthält der Seiteninhalt bereits `[newsletter_anfrage]`, wird es nicht zusätzlich angehängt.

**Variante B – Redaktion:** Auf einer berechtigten Seite einen WordPress-Block **Shortcode** einfügen und genau Folgendes eintragen:

```text
[newsletter_anfrage]
```

1. Vorhandenen Seitentext zur Kontaktaufnahme verständlich ergänzen; keine automatische Aufnahme versprechen.
2. Nur ein Newsletterformular auf der Seite verwenden und Vorschau prüfen.
3. Sicherstellen lassen, dass eine veröffentlichte Datenschutzerklärung hinterlegt ist. Ohne auflösbare Datenschutzseite ist das Formular gesperrt.
4. Eine kontrollierte Testanfrage mit freigegebener Testadresse durchführen und den tatsächlichen Eingang im Zielpostfach prüfen. Auf Testkopien dafür zunächst einen abgefangenen Test-Mailtransport einrichten lassen.
5. Seite veröffentlichen und die Administration gegebenenfalls den Menülink **Mach mit** setzen lassen.

Das Update allein legt keine fertige Live-Seite an und weist keiner bestehenden Seite automatisch diese Vorlage zu. Der feste Newsletterempfänger wird nicht durch ein Shortcode-Attribut oder das allgemeine KV-Kontaktfeld verändert.

### Anfragen bearbeiten – Geschäftsstelle

Dieser organisatorische Ablauf ist außerhalb des Themes einzurichten:

1. Die Anfrage als offenen Kontaktwunsch bearbeiten; die Adresse noch nicht in einen aktiven Versandverteiler aufnehmen.
2. Adressinhaberschaft und ausdrückliche Einwilligung in den konkret benannten Newsletter separat bestätigen lassen. Das freigegebene Double-Opt-in-Verfahren verwenden; ein abweichendes Verfahren zuvor mit der Datenschutzverantwortung klären.
3. Erst nach Bestätigung in den vorgesehenen Verteiler übernehmen. Wortlaut, Umfang und Bestätigungsnachweis geschützt dokumentieren.
4. Ohne Bestätigung keine Aufnahme vornehmen. Unbeantwortete oder unklare Anfragen nach dem abgestimmten Löschkonzept bearbeiten.
5. Bei Widerruf oder Abmeldung den Versand stoppen und den Verteiler korrigieren. Löschwünsche bearbeiten; gegebenenfalls notwendige Nachweise und minimale Sperrinformationen getrennt, zweckgebunden und befristet behandeln.

Die DSK erläutert Double-Opt-in, Einwilligungsnachweise, Widerruf und Löschwünsche. Die Formularmail ersetzt diese Schritte nicht; Zuständigkeit, Verfahren und Fristen sind verbindlich festzulegen. [DSK-Orientierungshilfe Direktwerbung, Februar 2022, Abschnitte 3.3, 3.7 und 5.1](https://www.datenschutzkonferenz-online.de/media/oh/OH-Werbung_Februar%202022_final.pdf).

### Kontaktformular und technische Grenzen

Das allgemeine Kontaktformular wird mit `[kontaktformular]` eingefügt. Es fragt Name, E-Mail, optionalen Betreff, Nachricht und Datenschutzbestätigung ab. Der Empfänger ist das Attribut `empfaenger`, sonst die KV-E-Mail, sonst die WordPress-Administrationsadresse. Das ist ein anderer Empfängerweg als beim festen Newsletterformular.

```text
[kontaktformular betreff="Anfrage an die Geschäftsstelle"]
```

Es gibt im Theme keinen Bildschirm „Newsletter-Abonnenten“ oder ein Posteingangsarchiv für Formulare. Das Theme speichert keinen Formular-Datensatz als Beitrag oder Abonnent. Die Nachricht wird jedoch im E-Mail-System verarbeitet und gegebenenfalls durch vorhandene Mail-/Protokoll-Plugins erfasst. Das muss die Datenschutzerklärung der realen Installation berücksichtigen.

Der Spam-Schutz umfasst ein unsichtbares Köderfeld, eine Sicherheitsprüfung, serverseitige Validierung und eine Begrenzung auf drei Nachrichten pro Stunde je serverseitig erkannter IP-Adresse. Dafür wird ein kurzlebiger Zähler mit gehashtem IP-Schlüssel verwendet; das ist kein dauerhafter Newsletter-Nachweis. Mehrere Personen hinter einem gemeinsamen Internetzugang können dieselbe Begrenzung treffen. Es gibt kein externes CAPTCHA und keinen zugesicherten vollständigen Spam-Schutz.

| Rückmeldung / Problem | Vorgehen |
|---|---|
| Sicherheitsprüfung fehlgeschlagen | Formularseite neu laden; bei wiederholtem Fehler Formular-Caching durch Administration prüfen |
| ungültige E-Mail oder fehlende Pflichtbestätigung | Eingabe beziehungsweise bewusste Bestätigung korrigieren |
| Formular noch nicht vollständig eingerichtet | Datenschutzseite und Empfänger durch Administration prüfen |
| Zu viele Nachrichten | später erneut versuchen; nicht wiederholt absenden |
| Nachricht konnte nicht gesendet werden | Mailtransport durch Administration prüfen, danach gezielt testen |
| Erfolg angezeigt, aber keine Nachricht angekommen | Eingang, Spamordner und Mailtransport kontrollieren; nicht automatisch in den Verteiler übernehmen |

Eine Erfolgsmeldung bedeutet nur, dass `wp_mail()` den Versandauftrag ohne gemeldeten Fehler verarbeiten konnte. Sie beweist keine Zustellung an das Postfach. Daher gehört ein Test des tatsächlichen Eingangs zur Inbetriebnahme. [WordPress-Referenz zu `wp_mail()`](https://developer.wordpress.org/reference/functions/wp_mail/).

## 12. E-Mail-Adressen für neue Vorstände

Das Theme **provisioniert keine Postfächer, Weiterleitungen oder Konten**. WordPress-Anmeldung, öffentliche Kontaktadresse und E-Mail-Postfach sind getrennte Dinge.

1. Die Geschäftsstelle sammelt den benötigten Funktionsnamen, KV/OV, gewünschte öffentliche Adresse, verantwortliche Person, Beginn/Ende der Zuständigkeit und den Bedarf an Postfach, Alias oder Weiterleitung. Keine Passwörter in die Anforderung aufnehmen.
2. Die Anfrage geht über den intern vereinbarten Weg an die zuständige E-Mail-/IT-Administration des Verbands beziehungsweise dessen Mailanbieter. Welche Stelle dies konkret übernimmt, muss der Kreisverband benennen; das Handbuch erfindet keinen Anbieter.
3. Die zuständige Stelle prüft Freigabe, Namensschema, Zugriffsberechtigung, Vertretung und Aufbewahrung. Sie richtet die Mailfunktion außerhalb von WordPress ein.
4. Zugang und gegebenenfalls zweiter Faktor werden persönlich über einen sicheren Kanal übergeben. Ein initiales Passwort bei Übergabe ändern; Zugangsdaten niemals in öffentliche Theme-Felder übernehmen.
5. Senden und Empfangen einschließlich Antworten testen. Erst dann die öffentliche Adresse im **KV-Setup** durch die Administration beziehungsweise unter **Verband → OV → Öffentliche Kontakt-E-Mail** durch Administration/OV-Admin eintragen.
6. Die Geschäftsstelle aktualisiert freigegebene Adressen in Seiten und Personenprofilen und prüft die öffentliche Ausgabe.
7. Beim Rollenwechsel Zugriffe neu zuweisen, aktive Sitzungen und gegebenenfalls Geheimnisse durch die zuständige Stelle erneuern. Öffentlich angezeigte Kontakte und WordPress-Rechte separat aktualisieren.
8. Beim Ausscheiden Zugriff zeitnah entziehen, Vertretung und notwendige Aufbewahrung klären und die Mailfunktion nach Freigabe deaktivieren oder überführen. Keine automatische unbegrenzte Weiterleitung an Privatadressen einrichten.

Die öffentliche Kontakt-E-Mail wird auf gültiges E-Mail-Format geprüft. Das beweist nicht, dass die Adresse existiert oder der Person gehört. Ein erfolgreicher Test bleibt erforderlich.

## 13. Homepage und Kreiskarte

### Homepage-Einstellungen – Administration

Die Geschäftsstelle liefert Texte, freigegebene Bilder und Ziel-URLs. Die Administration öffnet **Verband → Startseite** und bearbeitet dort die fest verwendete Variante **Neue Energie**; eine freie Auswahl beliebiger Homepage-Varianten bietet die aktuelle Oberfläche nicht.

1. Bisherige Einstellungen dokumentieren und einen passenden **Landing-Modus** wählen.
2. Die eingeblendeten Felder ausfüllen. *Hero* bezeichnet den großen Einstiegsbereich; *CTA* einen Handlungslink wie „Mitmachen“.
3. Bilder, Titel, Untertitel und Buttons prüfen. Für Buttons immer das tatsächliche Ziel öffnen.
4. Unter **Sektionen** Kreiskarte, Aktuelles und Termine ein-/ausblenden und die angebotene Anzahl festlegen.
5. **Speichern** wählen und Startseite einschließlich Mobilansicht prüfen. Einstellungsspeichern kann die öffentliche Homepage unmittelbar ändern und ist keine separate Entwurfsfreigabe.

| Landing-Modus | Wesentliche Angaben |
|---|---|
| Willkommen | Hero-Titel, Untertitel, Bild, primärer CTA und Intro-Inhalte |
| Wahlkampf | Wahlname, Wahltag, Slogan, Bild und CTA |
| Kandidat:in | Name, Amt/Rolle, Zitat, Foto und CTA |
| Aktuelles | Überschrift und Darstellung des neuesten Beitrags |
| Spendenaktion | Kampagnentitel, Beschreibung, manuell gepflegtes Ziel und Stand, Bild |
| Direkt zum OV | knapper Einstieg zur Kreiskarte |

Die WordPress-Zuweisung der eigentlichen Startseite unter **Einstellungen → Lesen** ist ein zusätzlicher Administrationsschritt. OV-Darstellung wird separat in **Verband → OV bearbeiten** mit Header, angebotenem Hero-Modus und Bereichen gepflegt. Ein KV-Homepagewechsel setzt nicht automatisch alle OV-Seiten um.

### Kreiskarte anzeigen – Redaktion

Auf einer berechtigten Seite einen Shortcode-Block einfügen:

```text
[kreiskarte max_width="700px"]
```

Die Daten der Karte müssen bereits eingerichtet sein. Die Anzeige kann zusätzlich in der Homepage-Konfiguration aktiviert werden. In der Vorschau die Gemeinde auswählen und das Linkziel kontrollieren. Für schlechte Erreichbarkeit per Tastatur oder mobile Darstellungsprobleme konkrete Gemeinde und Seite an die Administration melden; eine zusätzliche OV-Liste kann den Zugang erleichtern.

### Karte einrichten oder korrigieren – Administration

1. **Verband → Kreiskarte** öffnen. Vor dem Ersetzen die Datenbank sichern; bei älteren Theme-Versionen auch lokal geänderte Kartendateien sichern.
2. Falls keine Karte vorliegt, den Landkreis suchen, den richtigen Treffer wählen und die geladenen Gemeindegrenzen in der Vorschau kontrollieren.
3. Erst dann **Karte übernehmen** wählen.
4. Unter **Gemeinden konfigurieren** je Gemeinde die gewünschte Aktion wählen: **Ortsverband/Ortsgruppe** für lokale oder im OV-Kontaktfeld hinterlegte Websites, **Externer Link** für eine ausdrücklich vorgegebene Website, **Kontaktseite** für den Kontaktweg oder **Ohne Link** für einen Eintrag im Aufbau. Die Spalte **Klickziel auf der Website** zeigt die derzeit gespeicherte öffentliche Wirkung; ungespeicherte Änderungen sind markiert.
5. **Speichern** wählen. **Markierte Einträge anlegen** erzeugt zusätzliche Datensätze und ist kein bloßer Speicherschritt; vorher vorhandene OVs abgleichen.
6. Alle Gemeinden öffentlich testen: Karte und mobile Liste müssen die gespeicherte Aktion umsetzen. Für **Externer Link** ist eine gültige vollständige HTTP-/HTTPS-Adresse Pflicht; Platzhalter wie `example.com` durch die bestätigte Website ersetzen, niemals raten.

**Karte neu laden** erhält Zuordnungen nur, soweit Gemeinde-Slugs weiterhin übereinstimmen. Der Generator lädt OpenStreetMap-Daten. Gespeicherte Kartendaten liegen in der WordPress-Datenbank und bleiben bei einem Theme-Update erhalten; die mitgelieferte Kartendatei dient nur als Ausgangswert, solange keine eigene Konfiguration gespeichert wurde. Vor einem Update eine Datenbanksicherung und eine öffentliche Stichprobe der Ziele einplanen.

## 14. Shortcodes und Blöcke zum Nachschlagen

Ein *Shortcode* ist ein Platzhalter in eckigen Klammern. Im Blockeditor dafür den Block **Shortcode** verwenden; im klassischen Editor als eigene Textzeile einsetzen. Attributnamen und Slugs exakt schreiben, normale gerade Anführungszeichen verwenden und das Ergebnis in der öffentlichen Vorschau prüfen. Beispielslugs sind zu ersetzen; unbekannte Attribute haben keine zugesicherte Wirkung.

### Personen und Verbände

| Shortcode | Tatsächlich angenommene Attribute und Grenzen |
|---|---|
| `[abteilung slug="vorstand"]` | `slug` erforderlich; `abteilung` als alter Alias; `limit="0"` für alle; `zuordnung` für OV-Slug; `typ` als Altbestand-Fallback für Sortier-/Funktionsfelder |
| `[personenliste slug="vorstand"]` | Alias von `[abteilung]`, gleiche Attribute |
| `[vorstand]`, `[team]`, `[glv]`, `[mandate]`, `[landesliste]`, `[kontakt]` | ältere Personenansichten; `person="12,34"`, `slug` und `abteilung` werden angenommen; `slug` hat Vorrang vor `abteilung`. Sortierung nach älteren Positionsfeldern, kein eigenes `zuordnung`-Attribut; neue Bereichslisten vorzugsweise mit `[abteilung]` erstellen |
| `[ortsverband_liste]` | `mode="ov"`, `mode="ortsgruppe"` oder `mode="werbung"`; leer = alle; `full` wird als alter Wert für `ov` erkannt |
| `[gliederungen]` | `type` filtert den OV-Typ (`full` als Altwert für `ov`); Kontakt- und Social-Links; Namenslink mit gemeinsamer Auflösung einschließlich Informationsseite |
| `[arbeitsgemeinschaften]` | `type` filtert ebenfalls OV-Begriffe (`full` als Altwert für `ov`); keine eigene Arbeitsgemeinschaften-Datenbank; Namenslink mit gemeinsamer Auflösung einschließlich Informationsseite |

`[kontakt]` zeigt Personenkontakte, **kein Kontaktformular**. Auf OV-Seiten berücksichtigen `[abteilung]`, `[personenliste]` und die älteren Personen-Shortcodes automatisch den umgebenden OV. Außerhalb von OV-Seiten bedeutet bei `[abteilung]` leeres `zuordnung` eine bereichsübergreifende Liste mit möglichen OV-Filterreitern. Auch `zuordnung="kreisverband"` setzt in dieser Personenfunktion keinen strengen Nur-KV-Filter; das ist anders als bei Terminen. Für die Liste eines konkreten OVs den echten OV-Slug setzen. `limit` begrenzt die Gesamtmenge vor der Anzeige der Reiter.

### Termine, Anfragen, Karte und Spenden

| Shortcode | Attribute / Verhalten |
|---|---|
| `[termine]` | `anzahl="10"`, `kategorie=""` als Termin-Kategorie-Slug, `vergangen="nein"`, `zuordnung=""`; `vergangen="ja"` zeigt alle statt nur zukünftige Termine, absteigend |
| `[naechste_termine]` | kompakte Liste; `anzahl="5"`, `zuordnung=""`, `kategorie=""` |
| `[newsletter_anfrage]` | keine konfigurierbaren Attribute; fester Empfänger, Anfrage ohne automatische Anmeldung |
| `[kontaktformular]` | `empfaenger=""`, `betreff=""`, technisch auch `newsletter`; für Newsletter ausdrücklich den eigenen Shortcode verwenden. `newsletter="false"` ist kein sicherer Ausschalter, da der Code einen nichtleeren Text als wahr auswertet |
| `[kreiskarte]` | `class="kreiskarte"`, `max_width="700px"` |
| `[spenden]` | `titel`, `text`, `paypal`, `twingle`; leere Vorgaben kommen aus den Spendeneinstellungen; Bankdaten stammen aus der Konfiguration |
| `[spendenbalken ziel="1000" stand="450"]` | `ziel` und `stand` numerisch; Ziel muss größer als null sein; rein manuell gepflegte Anzeige |
| `[cookie_einstellungen text="Cookie-Hinweis erneut anzeigen"]` | `text`; löscht nur das Bestätigungs-Cookie und lädt die Seite neu |

`[twingle]` wird **nicht** vom Theme registriert. Es setzt das separat installierte und eingerichtete WP-Twingle-Plugin voraus; dessen Attribute und Einwilligungsmechanismen sind mit der Administration anhand der installierten Plugin-Version zu klären.

### Gestaltung und Unterseiten

| Shortcode | Attribute / Verwendung |
|---|---|
| `[unterseiten]` | keine Attribute; listet direkte untergeordnete Seiten mit Bild und Auszug, nach Menüreihenfolge |
| `[tabs]…[/tabs]` | umschließt Reiter, keine Attribute |
| `[tab title="Kontakt"]Text[/tab]` | `title`; innerhalb von `[tabs]` verwenden |
| `[infobox title="Hinweis"]Text[/infobox]` | `title`, Vorgabe „Infobox“ |
| `[box]Text[/box]` | farbige Box, keine Attribute |
| `[abstand]` | Abstand, keine Attribute |
| `[icon symbol="fa-rocket" groesse="fa-4x"]` | `symbol` und `groesse`; FontAwesome-Klassen, keine frei hochgeladene Grafik |
| `[parallax]` | `vollbild`, `hintergrundfarbe`, `schriftfarbe`, `id`, `bgscroll`, `hintergrundbild`; Farben ohne führendes `#`, Bild als URL; nichtleere `vollbild`/`bgscroll` aktivieren die jeweilige Klasse |

`[parallax]` greift in die Seitenstruktur ein und ist kein einfacher Container mit schließendem Tag. Bestehende Einsätze nur mit Vorschau bearbeiten; neue Layouts möglichst mit den normalen WordPress-Blöcken aufbauen. Beim Einsatz von Reitern Bedienung per Tastatur und Mobilansicht prüfen.

### Eigene Theme-Blöcke

| Block im Editor | Einstellungen | Verhalten |
|---|---|---|
| **Abteilung** (`gk/abteilung`) | `slug` als Abteilungsauswahl; `limit` als Zahl, 0 = alle | dynamische Personenliste; OV-Seitenbereich wird automatisch übernommen. Auf KV-Seiten kann die Liste mehrere Bereiche enthalten. Der Block bietet kein frei einstellbares `zuordnung`-Attribut |

Die Abteilungsauswahl eines OV-Blocks kann auf Abteilungen mit Personen dieses OVs begrenzt sein. Fehlt eine Abteilung, zuerst Personenzuordnung und Veröffentlichung prüfen. Ein eigener Termin-, Newsletter-, Spenden- oder Kreiskartenblock wird im Theme nicht registriert; diese Funktionen über den Standardblock **Shortcode** einsetzen.

## 15. SEO, Social Media und Vorschaubilder

*SEO* hilft Suchmaschinen, Seiten einzuordnen. *Open Graph* und *Twitter Cards* liefern Titel, Beschreibung und Bild für Linkvorschauen. Das Theme erzeugt diese Angaben weitgehend automatisch und bietet kein separates SEO-Eingabemenü für die Redaktion.

1. Einen klaren Titel und einen verständlichen ersten Absatz schreiben.
2. Bei Beiträgen, Seiten und Terminen den vorhandenen Textauszug sinnvoll ausfüllen. Ohne Auszug leitet das Theme die Beschreibung aus ungefähr 30 Wörtern des Inhalts ab; reine Shortcode-Seiten brauchen besonders sorgfältige Prüfung.
3. Ein passendes Beitragsbild mit geeignetem Ausschnitt setzen. Ohne Beitragsbild versucht das Theme auf Einzelinhalten das Website-Logo und gegebenenfalls ein vorhandenes Standardbild.
4. Überschriften geordnet verwenden und Links mit einem verständlichen Zieltext beschriften.
5. Nach Veröffentlichung die Seite und die gewünschte Linkvorschau prüfen. Social-Media-Plattformen können alte Vorschauen zwischenspeichern; eine Korrektur erscheint dort nicht immer sofort.

Das Theme erzeugt außerdem kanonische URLs und strukturierte Daten für Organisation, Website, Artikel, Termine, Personen und Seitennavigation. Das garantiert weder ein bestimmtes Suchmaschinenranking noch eine besondere Suchdarstellung. Zusätzliche SEO-Plugins können doppelte Angaben erzeugen; deren Zusammenspiel prüft die Administration.

KV-Profile werden im **KV-Setup**, OV-Profile unter **Verband → OV → Kontaktdaten**, persönliche Profile im Personeneintrag gepflegt. Die Eingabehinweise beachten: Manche Felder verlangen eine URL, andere erlauben einen Benutzernamen. Jeden erzeugten Link prüfen. Die Website veröffentlicht dadurch keine Beiträge automatisch auf den sozialen Netzwerken.

Logo, globale Darstellung und Dark-Mode-Vorgabe liegen im WordPress-Customizer und gehören zur Administrationsarbeit.

## 16. Spenden und Cookie-Hinweis

### Spenden – Redaktion pflegt Inhalte, Administration konfiguriert

1. Die Administration prüft unter **Verband → Spenden** Aktivierung, freigegebene Bankverbindung, Verwendungszweck, Twingle-/PayPal-URL und Buttontext mit der zuständigen Kasse.
2. Die Geschäftsstelle fügt auf einer berechtigten Seite nach Freigabe `[spenden]` ein und prüft die Vorschau.
3. Alle Buttons öffnen und Bankangaben zeichenweise gegen die Freigabe prüfen. Ein Test benötigt keine echte Zahlung.
4. Für eine Fortschrittsanzeige Ziel und Stand mit der Kasse abstimmen; `[spendenbalken]` oder der Homepage-Modus lesen keinen tatsächlichen Kontostand aus.

`[spenden]` zeigt Bankdaten und Links zu Twingle/PayPal, bettet aber selbst kein Zahlungsformular ein. `[twingle]` kommt aus einem separaten Plugin. Die bevorzugte allgemeine Spenden-Zielseite ist eine veröffentlichte Seite mit Slug `spenden`; ohne diese kann der allgemeine Spendenlink auf die konfigurierte PayPal-Adresse zurückfallen. Eine Twingle-URL allein erzeugt diese Seite nicht.

„Spenden aktiviert“ ist kein universeller Abschalter für bereits eingefügte Shortcodes oder fremde Plugin-Widgets. Soll eine Aktion enden, auch ihre Seiten, Menüs, Buttons und eingebetteten Formulare prüfen. Zahlungsabwicklung, Spendenquittungen, steuerliche Prüfung und Abrechnung führt das Theme nicht durch; pauschale Hinweistexte ersetzen die Auskunft der zuständigen Kasse nicht.

### Cookie-Hinweis: ausdrücklich kein allgemeines Einwilligungsmanagement

Der Banner enthält **Verstanden** und merkt sich die Bestätigung im Cookie `gk_cookie_consent` für ein Jahr. Er informiert über technisch notwendige Cookies. Er bietet keine Kategorienwahl, keine vorgelagerte Blockierung beliebiger Skripte und keine Kontrolle aller Plugins oder externen Einbettungen.

1. Vor Freigabe prüft die Administration die tatsächlich verwendeten Plugins, Zahlungsformulare, Karten, Videos, Analyse- und Drittanbieterressourcen.
2. Der Bannertext muss zur realen Installation passen. Sein Satz über ausschließlich notwendige Cookies beweist nicht, dass kein Plugin Tracking verwendet.
3. Wenn zusätzliche Dienste eine Einwilligung oder technische Blockierung erfordern, muss die Administration dafür ein passendes Verfahren einrichten. Der Theme-Banner erledigt dies nicht.
4. Die Geschäftsstelle kann `[cookie_einstellungen]` auf einer berechtigten Seite einfügen. Der Link entfernt ausschließlich die gespeicherte Bannerbestätigung und zeigt den Hinweis erneut.
5. Der Link widerruft keine Newslettereinwilligung, löscht keine Plugin-Cookies und deaktiviert keine externen Zahlungsformulare. Newsletterwiderrufe werden nach Abschnitt 11 bearbeitet.

Es gibt im Theme kein Cookie-Kategorienmenü für die Redaktion. Die Bezeichnung „Cookie-Consent“ in älteren Beschreibungen darf nicht als Zusage eines websiteweiten Consent-Managers gelesen werden.

## 17. Fehler, Rücknahme und Hilfe

| Beobachtung | Die Geschäftsstelle prüft selbst | Administration einschalten, wenn … |
|---|---|---|
| Inhalt fehlt in einer Liste | Bereich, Suche, Entwurf, Papierkorb und Veröffentlichungsdatum | Zuordnung falsch/mehrdeutig oder Rechte unerwartet sind |
| „Keine Berechtigung“ | richtiges Konto, richtiger Bereich, eigene/fremde Autorenschaft | der zulässige Bereich gesperrt bleibt; keine Administratorrolle als Abkürzung vergeben |
| Bild fehlt in der Auswahl, erscheint aber auf der Website | Bereich und bisherige Verwendung | Altzuordnung oder Dateibestand geprüft werden muss |
| Wiederhergestellter Inhalt ist nicht öffentlich | Status prüfen, Vorschau, bewusst neu veröffentlichen | Inhalt oder Zusatzfelder fehlen |
| OV-Link führt falsch oder ins Leere | genaue Liste, Gemeinde und Ziel-URL notieren | Homepage-Zuweisung, externe Website oder Kartenmapping korrigiert werden muss |
| Datenschutzlink führt nach München | Seiteninhalt und tatsächliche Linkadresse kontrollieren | KV-/OV-Seitenauswahl oder manuelle Menüs falsch sind |
| Termin fehlt | Startdatum, Status, Zuordnung, Kategorie und Listenanzahl | Datum gespeichert ist, aber Ausgabe/Feed falsch bleibt |
| Kalenderzeit verschoben | Editorzeit und importierten Termin vergleichen | Website-Zeitzone oder Kalenderinterpretation geprüft werden muss |
| Shortcode steht als Text oder zeigt nichts | Schreibweise, Shortcode-Block, echter Slug und Inhalt prüfen | benötigtes Plugin fehlt oder ältere Positionsfelder die Personenliste beeinflussen |
| Formular funktioniert nicht | Pflichtfelder, Seite neu laden, Ratenlimit | Datenschutz-Konfiguration oder Mailtransport betroffen sind |
| Seite trotz Änderung veraltet | neu laden und abgemeldetes Fenster testen | Website-/Proxy-Cache oder Social-Vorschau aktualisiert werden muss |
| Menüpunkte oder Einstellungen fehlen | Rollenmatrix abgleichen | eine konkrete Administrationsaufgabe erforderlich ist |

**Sichere Fehlermeldung an die Administration:** Website-/Seiten-URL, Inhaltstyp und ID, KV/OV, Rolle, Uhrzeit, genaue Fehlermeldung, kurze Schritte und erwartetes Ergebnis mitteilen. Einen Screenshot nur mit bereinigten Testdaten senden. Keine Passwörter, Tokens, vollständigen Formularnachrichten oder privaten Adressen in ein öffentliches Ticket oder ins Repository kopieren.

**Bei falscher Veröffentlichung:** Inhalt zuerst auf Entwurf setzen, Betroffene intern informieren und die Korrektur abstimmen. War eine vertrauliche Datei öffentlich, zusätzlich die Administration einschalten: Papierkorb oder geänderte Zuordnung sind keine Garantie, dass eine Datei oder zwischengespeicherte Kopie nicht mehr erreichbar ist.

**Bei technischen Fehlern nach einem Update:** Keine Theme-Dateien auf der Live-Seite überschreiben. Die Administration verwendet den vorab getesteten Rollback für Dateien und Datenbank. Die Geschäftsstelle dokumentiert den Fehler und prüft nach Wiederherstellung die betroffenen öffentlichen Abläufe.

## 18. Aufnahmeplan für Screenshots

Die folgenden Einträge sind **reproduzierbare Platzhalter, keine bereits angefertigten oder bestandenen Bildschirmtests**. Für Schulungsbilder eine Testinstallation mit Version 0.7.0, deutschsprachigem WordPress, Browserzoom 100 % und synthetischen Daten verwenden. Vorschlag für Desktop: 1440 × 900 Pixel; zusätzlich eine Mobilansicht mit etwa 390 Pixel Breite. Keine Passwörter, Sitzungsdaten, personenbezogenen Echtdaten oder echten Mailinhalte aufnehmen.

| Platzhalter / vorgeschlagener Dateiname | Konto und konkrete Aufnahme | Bildunterschrift / zu prüfender Zustand |
|---|---|---|
| S01 `01-dashboard.png` | Testkonto mit `gk_kvautor_ov`; Dashboard öffnen, linke Inhaltsnavigation sichtbar | „Die Geschäftsstelle wählt Inhaltstyp und KV-/OV-Bereich.“ |
| S02 `02-beitrag-entwurf.png` | Redaktions-Testrolle; Testbeitrag als Entwurf, Titel und Box Zuordnung sichtbar | „Vor Veröffentlichung die Zuordnung prüfen.“ |
| S03 `03-papierkorb.png` | denselben Testbeitrag in Papierkorb legen, Listenfilter öffnen | „Wiederherstellen ist verfügbar; dauerhaftes Löschen gehört nicht zur Redaktionsrolle.“ |
| S04 `04-theme-rollen.png` | Administrator; Benutzer → Theme-Rollen mit synthetischen Konten | „Rolle und Zuständigkeit ohne Passwörter oder E-Mail-Liste.“ |
| S05 `05-ov-website.png` | Administrator; Test-OV unter Verband öffnen, Startseiten- und Websitefeld aufnehmen | „Lokale Startseite und externe Website sind getrennte Angaben.“ |
| S06 `06-ov-pflichtseiten.png` | Administrator/OV-Admin; Impressum & Datenschutz mit KV-Rückfallauswahl | „Eigene veröffentlichte Seite oder bewusst KV-Seite übernehmen.“ |
| S07 `07-termin-editor.png` | Redaktions-Testrolle; fiktiver Termin mit Datum, Zeit, Ort und Zuordnung | „Termin-Details und Bereich vor Veröffentlichung prüfen.“ |
| S08 `08-termin-kalender.png` | öffentliche Test-Terminseite und separat geöffneter Test-iCal-Import | „Editor und Kalender zeigen denselben Zeitpunkt.“ |
| S09 `09-person-abteilungen.png` | Redaktions-Testrolle; synthetische Person, Abteilungenbox nach erster Speicherung | „Nr. verstecken blendet nur die Nummer aus.“ |
| S10 `10-medien-bereiche.png` | getrennte Aufnahmen mit der Rolle KV-Autor mit OV-Zugang und als OV-Testkonto, jeweils Medienliste | „Die Geschäftsstelle sieht alle Bereiche; das OV-Konto nur eindeutige eigene Zuordnungen.“ |
| S11 `11-medien-pruefung.png` | Administrator; Medien → Zuordnung prüfen vor einer Änderung | „Vorschau und geprüfte Einzelübernahme; kein automatischer Umbau.“ |
| S12 `12-newsletter.png` | öffentliche Testseite mit `[newsletter_anfrage]`, leeres Formular | „Anfrage an die Geschäftsstelle, noch kein Abonnement.“ |
| S13 `13-newsletter-meldung.png` | Test-Mailtransport abfangen; Testanfrage senden, Erfolg-/Fehlerzustand separat aufnehmen | „Die Rückmeldung ist keine Zustell- oder Abonnementbestätigung.“ |
| S14 `14-menues.png` | Administrator; Menüs → Test-OV mit Hauptmenü-/Footer-Zuweisung | „Menü und Anzeigeplatz gehören zusammen.“ |
| S15 `15-homepage.png` | Administrator; Verband → Startseite, Landing-Modus und Sektionen | „Globale Homepage-Einstellungen sind Administrationsaufgabe.“ |
| S16 `16-kreiskarte.png` | Administrator; Verband → Kreiskarte → Gemeinden konfigurieren | „Jede Gemeinde erhält ein geprüftes Ziel.“ |
| S17 `17-cookie-mobil.png` | abgemeldete Mobilansicht, Bannerbestätigung zuvor zurücksetzen | „Verstanden bestätigt den Hinweis; keine Plugin-Einwilligungsverwaltung.“ |
| S18 `18-gemeinde-klickwege.png` | je Gemeinde Desktop-Karte, mobile Liste/Detailansicht und Tastaturziel aufrufen; nur synthetische Daten | „Tatsächlicher Klick führt auf die erwartete OV-Seite; URL, Status und OV-Kontext sind geprüft.“ |

Vor jeder Aufnahme den Schritt aus dem zugehörigen Kapitel ausführen und die Bildunterschrift mit dem wirklichen Zustand abgleichen. Falls die Oberfläche abweicht, das Handbuch anpassen statt ein erwartetes Bedienelement in das Bild einzubauen.

## 19. Vor der Freigabe und Quellen

### Noch installationsbezogen festzulegen

| Punkt | Zuständige Stelle / benötigter Nachweis |
|---|---|
| Live-Installation 0.7.0 | Administration: installierte Version, getestetes ZIP, Backup und Rollback |
| Rolle der Geschäftsstelle | Administration: passende Theme-Rolle und Zuständigkeit prüfen; tatsächlichen Anmelde-/Rechtetest durchführen |
| Mach-mit-Seite | Seiten-URL, Vorlage oder Shortcode, Menüplatz und veröffentlichte Datenschutzerklärung |
| Mailtransport | tatsächlicher Eingang einer kontrollierten Testanfrage bei der konfigurierten Empfängeradresse |
| Newsletterbetrieb | verantwortliche Person/Vertretung, getrennte Adress- und Einwilligungsprüfung, Verteiler, Nachweis- und Löschkonzept |
| Vorstands-Mailadressen | benannte Mailadministration, sicherer Übergabeweg, geregelter Entzug beim Ausscheiden |
| Altmedien | geprüfte Zuordnungen, dokumentierte Einzelübernahmen, erfolgreicher Rücknahmetest, keine automatisch als korrekt angenommene Migration |
| Pflichtseiten | aktuelle Texte, richtige lokale KV-/OV-Ziele, keine alten Fremdverweise |
| Gemeinden und OV-Einstiege | je Gemeinde Klicktests aus Liste, Karte, Mobilansicht und per Tastatur mit Ziel-URL, Status und sichtbarem OV-Kontext |
| Spenden, Plugins, Cookie-Aussage | aktuelle Plugin-Konfiguration, Datenflüsse und Freigabe durch die zuständigen Stellen |
| Screenshots und praktische Abläufe | Aufnahmeplan auf Testinstallation ausführen; Bilder sind hier noch Platzhalter |

Vor dem regulären Update prüft die Administration auf einer Testkopie Startseite, mobile Navigation, KV-/OV-Seiten, externe OV-Links, Pflichtseiten, Beiträge einschließlich Papierkorb, Termine und iCal, Personenlisten, Medienauswahl, Newsletter-/Kontakttransport und Spendenlinks. Die Prüfung muss auch mit der Rolle der Geschäftsstelle und mindestens zwei unterschiedlichen OV-Konten erfolgen. Ein Quellcodeabgleich dieses Handbuchs ersetzt keinen solchen Test.

### Grundlage und bekannte Grenzen

Dieses Handbuch wurde mit [README](../README.md), [CONTRIBUTING](../CONTRIBUTING.md) und den tatsächlichen Theme-Dateien abgeglichen, insbesondere:

- [Rollen](../theme/inc/roles.php), [Bereichs- und Löschschutz](../theme/inc/role-security.php), [Theme-Rollenübersicht](../theme/inc/role-overview.php).
- [Medienbereiche](../theme/inc/media-scope.php), [Medienprüfung und Rücknahme](../theme/inc/media-review.php).
- [Admin-Oberflächen](../theme/inc/admin.php), [OV-Verknüpfung und Pflichtseiten](../theme/inc/ortsverband.php), [KV-Setup](../theme/inc/setup-wizard.php).
- [Termine und iCal](../theme/inc/events.php), [Personentypen und Zuordnungen](../theme/inc/post-types.php), [Abteilungsangaben](../theme/inc/abteilung-meta.php).
- [Anfrageformulare](../theme/inc/contact-form.php), [Mach-mit-Vorlage](../theme/page-templates/mach-mit.php).
- [Shortcodes](../theme/inc/shortcodes.php), [Abteilungsblock](../theme/inc/blocks.php).
- [Homepage-Einstellungen](../theme/template-parts/home/neue-energie-settings.php), [Kreiskarte](../theme/inc/kreiskarte-generator.php), [SEO](../theme/inc/seo.php), [Spenden](../theme/inc/donation.php), [Cookie-Hinweis](../theme/inc/cookie-consent.php).

Besonders zu beachten sind die beschriebenen Alt-Personenshortcodes, die unterschiedliche Bedeutung des KV-Filters bei Personen und Terminen, die fehlenden Menü-/Customizerrechte auch der OV-Admin-Rolle, die zeitlich und mengenmäßig begrenzten Kalenderfeeds sowie die fehlende websiteweite Einwilligungssteuerung. Den technischen Abgleich und die praktischen Prüfungen dokumentieren der [Releasebericht](RELEASE-0.7.0.md) und der [unabhängige Review](validation/INDEPENDENT-REVIEW.md).
