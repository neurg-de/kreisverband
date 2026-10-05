# Version 0.9.0

OV-Redaktion kann die eigene Startseite gestalten, eigene Rubriken verwalten und
Rubrikarchive im eigenen Hauptmenü verlinken. Ein OV-Admin oder OV-Autor mit
gültiger Zuordnung öffnet **OV-Startseite**. Allgemeine Kategorien-, Menü- oder
Administrationsrechte sind dafür nicht erforderlich.

- Sektionen mit Auf/Ab-Buttons oder Ziehen sortieren und einzeln ausblenden,
  einschließlich des zusätzlichen Seiteninhalts.
- Titelbildtext und gegebenenfalls verwendetes OV-Label ausblenden oder einen
  eigenen Titel verwenden; Bildpflege bleibt im bisherigen Editor.
- 3 bis 20 Beiträge anzeigen, **Alle Beiträge** aktivieren und eigene Rubriken
  aus der Startseitenliste ausschließen. Beiträge bleiben veröffentlicht.
- Team-Gruppen sortieren, ohne Personenpositionen oder Funktionen zu ändern.
- Eigene Kategorien anlegen und umbenennen. Ein Besitzer-Metafeld begrenzt sie
  auf den OV. Löschen bleibt ausschließlich bei der Administration, auch bei
  leeren Rubriken, damit verlinkte Archive nicht versehentlich verschwinden.
- Im ausschließlich diesem OV zugewiesenen Hauptmenü einzelne oberste Links
  hinzufügen, ersetzen oder entfernen. Seiten und Beiträge werden dabei nicht
  gelöscht. Geteilte Menüs und Menüplätze bleiben bei der Administration.
- OV- und Rubrikarchive verwenden dieselbe Kartenliste mit Paginierung.
  Stabile Query-URLs funktionieren auch ohne sprechende Permalinks und verdrängen
  keine bestehenden Seitenpfade. Fremde oder unbekannte Kombinationen ergeben 404.

## Kompatibilität und Rechte

Ohne neue gespeicherte Konfiguration bleiben Sektionsfolge, Sichtbarkeit,
sechs Nachrichten, Titelbild und alphabetische Team-Gruppen wie bisher.
Der Archivlink wird erst mit der neuen Konfiguration eingeschaltet. Die bisherigen
Bereichscheckboxen bleiben als Rückfall erhalten; gespeicherte neue Sektionen
haben Vorrang. Es gibt keine destruktive Inhaltsmigration und keine Änderung
an Benutzerrollen oder den Rechten der Geschäftsstelle.

Die objektgebundene Capability `gk_edit_ov_home` prüft Rolle und genaue
`gk_zuordnung`. Sie gewährt weder `manage_categories` noch `edit_theme_options`.
Kategoriewrites laufen über einen eigenen, noncegeschützten Editor mit
Besitzerprüfung. WordPress-Kategorienverwaltung und globaler Menüeditor bleiben
für OV-Konten gesperrt. REST-/klassische-/Mehrfach-Zuordnungen dürfen keine
Rubriken anderer OVs neu hinzufügen. Bestehende Zuordnungen werden erhalten.

Dieses Release übernimmt außerdem die zuvor vorhandene Team-Karussell-Darstellung
und die ausdrückliche Personenkennzeichnung aus 0.8.2. Der automatisierte Ablauf,
Pausen, reduzierte Bewegung, Abteilungspositionen und öffentliche Kennzeichnung
bleiben erhalten.

## Prüfungen

- PHPUnit unter PHP 8.3 / WordPress 6.9.4: 126 Tests, 655 Assertions erfolgreich.
  Neun neue Tests decken Standardwerte, Whitelists, Darstellung, alle sechs
  Titelbildmodi, Teampositionen, OV-A/B/KV-Rechte, Kategorienbesitz,
  Menüzuweisung, Paginierung, statische Startseite und manipulierte URL-Parameter ab.
- WordPress Coding Standards: keine Fehler oder Warnungen.
- JavaScript-Syntax, Sass-Build, Versionsabgleich, ZIP-Struktur erfolgreich.
- Browserprüfung mit synthetischem OV-Admin: 21 erfolgreiche Prüfungen,
  darunter echte Enter-/Leertastenbedienung mit erhaltenem Fokus, Speichern,
  Rubrik anlegen/umbenennen, Menülink ersetzen, Mehrfachbearbeitung,
  gesperrte Core-Verwaltung, Archivseiten, 1280/390-Pixel-Ansichten und HTTP 403 bei fehlender Nonce.
- Unabhängiger zweiter Agent: Rechte-/Sicherheitsreview. Gefundene Routing-,
  Archivscope-, Typvalidierungs- und Standarddarstellungsfehler behoben;
  Nachprüfung ohne offene Sicherheits- oder Releaseblocker. Eigenständige
  PHP-/JavaScript-Syntax- und Diff-Prüfungen erfolgreich.

- Separate ZIP-Testinstallation: reguläres Update von 0.8.2 auf 0.9.0;
  unkonfigurierter OV erzeugt dieselbe HTML-Ausgabe nach Normalisierung der
  Assetversionen. Datei- und Datenbankbackup wiederhergestellt: Version 0.8.2
  und ursprüngliche HTML-Ausgabe identisch.

## Anleitung, Update und Rücknahme

[OV-Startseite gestalten](BENUTZERHANDBUCH.md#ov-startseite-gestalten) beschreibt
Klickwege, Mehrfachbearbeitung, Grenzen der Rechte und Rücknahme.
Screenshot-Platzhalter verwenden ausschließlich synthetische Daten.

Vor einer Installation eigenes Datei- und Datenbankbackup und einen erreichbaren
Restoreweg prüfen. Das ZIP als reguläres WordPress-Theme-Update auf einer separaten
Testinstallation prüfen, danach auf der Zielinstallation installieren. Eine
GitHub-Veröffentlichung bestätigt keine Live-Installation.

**Bisherige Darstellung wiederherstellen** entfernt nur die neuen
Startseitenoptionen. Rubriken, Beiträge und Menüs bleiben bestehen; einen
versehentlich entfernten Seitenlink setzt die Administration erneut. Bei einem
Theme-Rollback werden die neuen Einstellungen nicht gelöscht. Neue Rubriklinks
benötigen für ihre Archivdarstellung diese Theme-Version; bei einem Rollback die
Menülinks entsprechend prüfen.
