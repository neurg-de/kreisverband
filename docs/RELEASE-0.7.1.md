# Release 0.7.1 – OV-Navigation und Gestaltung

Gemeinden ohne eigene Website erhalten eine erreichbare Informationsseite mit
OV-Kontakt oder dem hinterlegten Kontakt der Geschäftsstelle. Liste, Karte und
mobiler Dialog verwenden konsistente Ziele: ausdrücklich konfigurierte gültige
externe Kartenwebsite, sonst veröffentlichte interne Homepage, externe
Kontaktwebsite oder Informationsseite. Platzhalter und ungültige URLs werden
verworfen; der mitgelieferte Kartenplatzhalter wurde entfernt.

Personen-Shortcodes übernehmen auf OV-Seiten deren Zuordnung. Sie zeigen dort
keine Personen anderer OVs mehr an. Allgemeine Übersichtsseiten behalten ihre
bereichsübergreifende Ansicht.

Das Rollenupgrade ergänzt fehlende Upload-Rechte bestehender Redaktionsrollen,
ohne Konten umzubenennen oder Zuständigkeiten zu erweitern. OV-Admins finden
unter **Seiten → Vorlagen & Gestaltung** die Anleitung zur Seitenvorlage und
drei Inhaltsvorlagen im Blockeditor. Globale Designrechte bleiben gesperrt.
Die administrative Rollenübersicht zeigt zusätzlich tatsächliche Fähigkeiten
und die Zahl bearbeitbarer eigener beziehungsweise fremder Inhalte.

## Prüfung

- PHPUnit: 103 Tests, 463 Assertions erfolgreich, einschließlich zuvor
  fehlschlagender Regressionen für URL-Platzhalter, fehlende Navigationsziele,
  Rollenupgrade und eingebettete Personenlisten.
- REST-Tests erlauben OV-Seitenvorlagen im eigenen Bereich und verweigern sie
  für fremde Seiten. Bestehende Medien- und Bereichstests bestehen weiterhin.
- PHP-Syntax, PHPCS, JavaScript-Syntax, Sass-Build und ZIP-Struktur geprüft.
- Browserprüfung: 28 Gemeinde-/Viewport-Kombinationen mit echter Maus- und
  Tastaturbedienung, einschließlich mobiler Liste, Dialog-CTA, Tab-Fokus und Escape.
  Keine JavaScript-Laufzeitfehler.
- Reguläres ZIP-Update von 0.7.0 auf 0.7.1 sowie Wiederherstellung von Dateien und
  Datenbank in einer isolierten Testinstallation geprüft.

Der Browser-Harness `bin/smoke-ov-navigation.cjs` prüft alle konfigurierten
Gemeinden bei 1280 und 390 Pixeln mit Maus, Tastatur, mobilem Dialog,
Fokuswechseln und HTTP-Status. Er benötigt separat installiertes Playwright
und Chrome; `GK_SMOKE_URL` wählt die zu prüfende Website.

## Installation

Release-ZIP und SHA256SUMS verwenden. Vor dem Update Dateien und Datenbank
sichern und den Rückrollweg prüfen. Installationsspezifische Kartendaten vor
und nach dem Update vergleichen; die Theme-Datei wird beim Paketupdate ersetzt.
Die Informationsseite benötigt gepflegte Kontaktangaben. Das Theme legt keine
Benutzerkonten an, setzt keine Passwörter zurück und verschickt beim Update
keine Nachrichten. Die konkrete Installation und ihre Rechte separat prüfen.
