# Repository-Regeln

## Öffentliche Dokumentation und interne Aufträge trennen

Dieses Repository ist ein generisches, öffentliches WordPress-Theme, das von
beliebigen Kreis- und Ortsverbänden verwendet werden kann. Die konkret betreute
Website ist eine Anwenderinstallation, nicht die Identität des Projekts.

- Gemeinsam nutzbare Funktionen, Schnittstellen und Dokumentation gehören in
  das Repository. Installationsspezifische Domains, Empfänger, Konten, Inhalte
  und Deployment-Zugänge gehören in die Konfiguration der jeweiligen Installation.
  Neue Funktionen dürfen diese Werte nicht für einen einzelnen Anwender fest
  im Theme verdrahten; vorhandene Konfigurationswege nutzen oder generische
  Einstellungen ergänzen.
- Kundenspezifische Betriebsaufträge nicht mit Produktanforderungen gleichsetzen.
  Änderungen am gemeinsamen Theme und Änderungen an einer Anwenderinstallation
  getrennt umsetzen, prüfen und dokumentieren. Eine öffentliche Release-Notiz
  beschreibt Produktverhalten, keine interne Übergabe an eine bestimmte Person
  oder einen bestimmten Verband.

- Handbücher, README, Release Notes, Beispiele und öffentliche Prüfberichte
  generisch für Redaktion, Geschäftsstelle, OV-Verantwortliche und Administration
  schreiben. Keine Namen konkreter Mitarbeitender, persönlichen Anreden oder
  personenbezogenen Rückmeldungen aus internen Gesprächen übernehmen.
- Persönliche Arbeitsaufträge, interne Nachrichten, tatsächliche Kontozuordnungen,
  lokale Benutzerpfade und installationsbezogene Übergabeprotokolle außerhalb
  des öffentlichen Repositories aufbewahren. Vor Veröffentlichung nur die
  sachlichen Anforderungen und anonymisierten Prüfergebnisse übernehmen.
- Beispiele und Screenshots ausschließlich mit synthetischen Daten erstellen.
  Funktionsbeschreibungen müssen trotzdem dem tatsächlichen Code entsprechen;
  konkrete Empfänger oder Konfigurationswerte nicht durch irreführende Beispiele
  ersetzen. Im generischen Handbuch auf die Prüfung der Konfiguration verweisen.
- Keine Zugangsdaten, Tokens, Sitzungscookies, Backups oder Produktionsdaten
  committen oder in öffentliche Release-Artefakte aufnehmen.
- Vor Commit, PR und Release die geänderte Dokumentation sowie Release-Texte
  auf personenbezogene Bezüge und interne Betriebsinformationen prüfen.
  Bei Korrekturen bereits veröffentlichter Dokumentation auch den konkret
  verlinkten Versionsstand prüfen, nicht nur den Entwicklungsbranch.

Diese Trennung ist eine dauerhafte Vorgabe für alle künftigen Arbeiten in diesem
Repository. Individuelle Betriebsaufträge bleiben davon getrennt.
