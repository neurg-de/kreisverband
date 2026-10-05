# Unabhängiger Review: OV-Startseiteneditor

Ein zweiter Agent hat den Editor, das Capability-Mapping, Kategorie- und
Menüdienste, das Archivrouting sowie die Darstellung read-only geprüft.

Gefundene und behobene Befunde:

1. Globale Rewrite-Regeln konnten bestehende Unterseiten verdrängen.
   Die Archive verwenden jetzt ausschließlich stabile Query-URLs.
2. Zusätzliche Singular-Parameter konnten die Taxonomiegrenze der Archivquery
   umgehen. Der Hauptquery wird aus einer Whitelist neu aufgebaut;
   Regressionstests umfassen fremde Beitrags-IDs und eine statische Startseite.
3. Leere Startseiten-News erzeugten einen zusätzlichen Bereich.
   Ohne neue Konfiguration bleibt der Bereich wie zuvor aus.
4. Array-Parameter im öffentlichen Archiv konnten PHP-Typfehler auslösen.
   Typprüfung und kontrollierte 404-Ausgabe decken ungültige Kombinationen ab.
5. Eigener Titel und Textsichtbarkeit waren zwischen Einstiegsmodi uneinheitlich.
   Alle sechs Modi wurden korrigiert und getestet, einschließlich eines
   eigenen Titels im News-Modus ohne vorhandenen Beitrag.

Zusätzliche Nachprüfung: eigener News-Titel ohne Beiträge und frühe
POST-Capability-/Nonce-Prüfung vor dem Admin-Header korrekt. Fehlende Nonces
werden mit HTTP 403 abgewiesen; der Callback prüft weiterhin erneut.

Nachprüfung: keine offenen Sicherheits- oder Releaseblocker. Keine zusätzliche
Rechteeskalation in OV-A/B/KV-Capability, Nonces, Besitzerprüfung, REST-/klassischen-
/Mehrfach-Zuordnungen, Menübindung oder fremden Menüpunkt-IDs gefunden.

Vom Reviewer eigenständig ausgeführt: PHP-Lint für zwölf Dateien,
JavaScript-Syntaxprüfung für beide betroffenen Skripte, `git diff --check`.
Alle erfolgreich. Der Reviewer führte keine DB-mutierenden Tests aus.

Die PHPUnit- und Browserergebnisse stammen aus den separat ausgeführten
Implementierungsprüfungen; siehe Releasebericht und PHPUnit-Ausgabe.
