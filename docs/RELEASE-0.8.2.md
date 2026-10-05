# Version 0.8.2

Die Teamautomatik läuft nach zwölf Sekunden ohne Mausbewegung oder manuelle
Bedienung wieder weiter. Ein ruhender Mauszeiger und per Maus fokussierte Buttons
blockieren sie nicht mehr dauerhaft. Ein ausdrücklicher Start funktioniert auch,
während der Startbutton den Fokus behält. Tastaturbedienung pausiert die Automatik;
reduzierte Bewegung und eine ausdrücklich gewählte Pause werden weiterhin beachtet.

Die kompakte Mitgliederreihe zeigt ganze Karten ohne sichtbare Scrollleiste.
Pfeile, horizontales Wischen und die vollständige Übersicht bleiben verfügbar.
Alle Mitglieder einer Abteilung erscheinen vor dem Wechsel zur nächsten Gruppe.

OV-Startseiten übernehmen keinen automatischen Seitenauszug mehr ins Titelbild.
Der Einführungstext erscheint darunter. Ein bewusst eingetragener Untertitel ist
in den dafür vorgesehenen Einstiegsmodi weiterhin möglich. Das Bearbeitungsformular
erläutert die Trennung. Ein Team-Bereich ist für einen Einführungstext nicht nötig.

## Prüfung

Browserprüfungen mit synthetischen Mitgliedern bei 1280, 768 und 390 Pixeln Breite:
automatischer Durchlauf aller 22 Mitgliedschaften, konstante Reihenhöhe, ruhender
Mauszeiger, Pause, Neustart mit Maus und Tastatur, Wiederaufnahme nach manueller
Navigation, vollständige Übersicht und reduzierte Bewegung. Zusätzlich wurde
horizontales Wischen mit Touch-Ereignissen geprüft. Das wiederverwendbare Skript
`bin/smoke-team-carousel.cjs` erwartet eine Testseite mit mehreren gefüllten
Team-Abteilungen und verwendet eine separat installierte Playwright-Umgebung.

Die OV-Vorlage wurde in allen sechs Einstiegsmodi mit leerem und ausdrücklich
eingetragenem Untertitel geprüft. PHP-Codeprüfung, JavaScript-Syntaxprüfung und
der ZIP-Build sind erfolgreich.
