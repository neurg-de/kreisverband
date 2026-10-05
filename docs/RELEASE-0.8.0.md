# Version 0.8.0

Die Teamdarstellung auf OV-Startseiten zeigt die Mitglieder als einzelne horizontal
scrollbare Reihe. Dieselbe Komponente lässt sich mit
`[team_carousel abteilungen="vorstand,fraktion"]` in Seiteninhalte einbinden.
Die Slugs müssen den Abteilungen der jeweiligen Installation entsprechen.

- Abteilungsreiter, Funktionen und Reihenfolge stammen aus der Personendatenbank.
  Es gibt keine Begrenzung auf die ersten 60 Personen.
- Die Automatik blättert alle sechs Sekunden weiter und wechselt erst nach dem
  Ende einer Mitgliederreihe zur nächsten Abteilung.
- Mauszeiger, Tastaturfokus, manuelle Bedienung und unsichtbare Ansichten pausieren
  den Ablauf. Eine Pause-Schaltfläche und reduzierte Bewegung werden berücksichtigt.
- „Alle anzeigen“ öffnet alle Gruppen als Raster; „Kompakt anzeigen“ schließt sie.
- Startseiteninhalte erscheinen in allen Hero-Modi, auch im Modus „Minimal“.

Personen können im Editor unter „Parteizugehörigkeit“ ausdrücklich als
parteifremdes Fraktionsmitglied oder parteilos gekennzeichnet werden. Die
Kennzeichnung erscheint auf Profilen und Personenkarten. Die öffentliche
OV-Zugehörigkeit und die pauschale Parteizugehörigkeit in den strukturierten
Personendaten entfallen. Interne Zuordnung und Bearbeitungsrechte bleiben erhalten.

## Prüfung

Die WordPress-Tests prüfen unter anderem veröffentlichte Mitgliedschaften,
Abteilungsreihenfolge, unterschiedliche Funktionen derselben Person, mehr als
60 Mitglieder, OV-Abgrenzung, Speicherung der Kennzeichnung mit Berechtigungs-
und Nonce-Prüfung sowie die getrennte öffentliche und interne Zuordnung.
Browserprüfungen mit synthetischen Personen decken automatischen Durchlauf,
Tastatursteuerung, Pausen, reduzierte Bewegung, vollständige Übersicht und eine
mobile Ansicht mit 390 Pixeln Breite ab.

## Update

Das ZIP als reguläres Theme-Update installieren. Bestehende Personen müssen nicht
migriert werden. Abteilungen und gewünschte Kennzeichnungen redaktionell prüfen.
Es werden keine Personen automatisch als parteifremd oder parteilos eingestuft.
