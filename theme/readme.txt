=== Neurg Kreisverband ===

Contributors: severinkistner
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.11.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress-Theme für GRÜNE Verbände und ihre Untergliederungen.

== Description ==

Neurg Kreisverband ist ein spezialisiertes, kostenloses WordPress-Theme für GRÜNE Verbände und ihre Untergliederungen. Entwickelt und gepflegt unter [neurg.de](https://neurg.de).

Funktionen:

* Personendatenbank mit Abteilungen und Zuordnungen
* Interaktive SVG-Verbandskarte für Gemeinden, Stadtbezirke und größere Gebiete (OpenStreetMap-basiert)
* Verwaltung von Untergliederungen mit eigenem Routing und Templates
* Termin-/Eventmanagement mit iCal-Export
* Flexible Shortcodes (Vorstand, Mandate, Landesliste, Gliederungen, u.v.m.)
* Eingebautes SEO (Open Graph, JSON-LD, Meta-Descriptions)
* Spenden-Integration (Twingle, Bankverbindung)
* Kontaktformular mit Spam-Schutz
* Cookie-Consent-Banner (DSGVO)
* Social-Media-Integration (Instagram, Facebook, X, TikTok, Threads, Mastodon, Bluesky)
* Benutzerdefinierte Rollen: Kreisadmin, OV-Admin, OV-Autor
* Setup-Wizard fuer Ersteinrichtung

== Installation ==

1. Theme-Ordner nach `wp-content/themes/neurg-kreisverband` kopieren oder als ZIP im WordPress-Admin hochladen.
2. Unter Design > Themes das Theme „Neurg Kreisverband" aktivieren.
3. Den Setup-Wizard im Dashboard durchlaufen (Impressum, Datenschutz, KV-Details).
4. Navigationsmenüs unter Design > Menüs einrichten.

== Frequently Asked Questions ==

= Fuer welche WordPress-Version ist das Theme geeignet? =

Ab WordPress 6.4 mit PHP 8.1 oder hoeher.

= Wie richte ich eine Untergliederung ein? =

Als Administrator unter Verband eine Untergliederung anlegen und eine lokale Startseite oder eine externe Kontakt-Website hinterlegen. Anschliessend die Links in Liste und Verbandskarte pruefen. Das vollstaendige deutsche Benutzerhandbuch liegt im Repository unter docs/BENUTZERHANDBUCH.md.

= Welche Plugins werden benoetigt? =

Keine. Alle Funktionen (Events, SEO, Kontaktformular, Cookie-Consent) sind im Theme integriert.

== Changelog ==

= 0.11.1 =
* Kürzere Rubrikadressen unterhalb der Bereichsstartseite (/{bereich}/rubrik/{rubrik}/); bestehende Query-Links bleiben gültig, echte Seiten behalten Vorrang.
* Bereichs-Termine-Seite mit Ansicht der vergangenen Termine.
* Automatische Bereichsmenü-Einträge wahlweise am Anfang des Menüs.
* Größere Tippfläche für den Zurück-Link zum Hauptverband.

= 0.11.0 =
* Eigene Termine- und Mitmachen-Seiten im Untergliederungsbereich mit lokalen Kontakt- und Social-Links; pro Bereich aktivierbar, bestehende Seiten behalten Vorrang.
* Automatische Bereichsmenü-Einträge mit konfigurierbaren Labels sowie REST-Zugriff auf die neuen Einstellungen.
* Seiten und ihre Unterseiten lassen sich in der Bereichsseitenleiste ausblenden, ohne sie zu löschen.
* Neutrale Initialen für fehlende Personenbilder und Hinweis auf die verfügbare Beitragszahl im Startseiteneditor.
* Newsletter-Empfänger installationsbezogen konfigurierbar; keine fest verdrahtete Adresse im Theme.
* Verbesserte mobile Navigation, Tastaturbedienung, Kontraste, Social- und Teilen-Schaltflächen.

= 0.10.0 =
* Zwei verpflichtend ausgewählte Verbandsebenen mit konfigurierbaren Bezeichnungen, Anzeige- und URL-Präfixen.
* Verbandskarte für Gemeinden, Stadtbezirke und größere Gebiete mit mehrteiligen Grenzen und stabilen Zuordnungen beim Neuladen.

= 0.9.0 =
* Eigene OV-Startseiten mit tastaturbedienbarer Sektionsreihenfolge und Sichtbarkeit.
* Titelbildtext, Beitragszahl, Archivlink, Rubrikausschlüsse und Team-Gruppen pro OV.
* Eigene Rubriken und Rubriklinks im eigenen OV-Hauptmenü ohne globale Verwaltungsrechte.
* Paginierte OV- und Rubrikarchive mit stabilen, kollisionsfreien Query-URLs.
* Bestehende Teamdarstellung und Personenkennzeichnung aus 0.8.2 bleiben erhalten.


= 0.8.2 =
* Die Teamautomatik setzt nach einer Bedienpause wieder ein und lässt sich auch vom fokussierten Startbutton starten. Kompakte Reihen zeigen ganze Karten ohne sichtbare Scrollleiste.
* OV-Startseiten wiederholen den Seiteninhalt nicht mehr automatisch im Titelbild. Ein expliziter Untertitel bleibt optional; die Bearbeitungsmaske erläutert das Verhalten.

= 0.8.1 =
* Teamkomponenten im Startseiteninhalt verwenden denselben flächigen Hintergrund und dieselbe Inhaltsbreite wie OV-Startseiten.


= 0.8.0 =
* Redaktionelle Kennzeichnung parteifremder und parteiloser Fraktionsmitglieder ohne öffentliche OV-Zugehörigkeit.
* Gemeinsame kompakte Teamkomponente für OV-Startseiten und den Shortcode [team_carousel].
* Horizontale Mitgliederreihe mit Abteilungsreitern, automatischem Durchlauf, Pause und aufklappbarer Gesamtübersicht.
* Abteilungsbezogene Funktionen und Reihenfolge; alle veröffentlichten Mitglieder werden berücksichtigt.
* Startseiteninhalte werden in allen Hero-Modi angezeigt.


= 0.7.5 =
* Reine Terminzuordnungen erscheinen ausschliesslich in der Terminverwaltung, nicht als Bereiche fuer Seiten, Beitraege, Personen, Medien oder Menues.
* Karteneditor kennzeichnet "Nur Termine" ausdruecklich. Bestehende vollstaendige OV-Bereiche bleiben unveraendert.
* Zuordnungsauswahl in klassischem Editor und Blockeditor folgt dem Inhaltstyp; bestehende Termine und Kartenlinks bleiben erhalten.

= 0.7.4 =
* Terminmenue je Gemeinde unabhaengig von Kartenlink und lokaler OV-Unterseite aktivieren.
* Fehlende Terminzuordnungen beim Speichern anlegen, ohne oeffentliche Seiten zu erzeugen.
* Neue Termine uebernehmen den ausgewaehlten Verband; zentrale Liste "Alle Termine" ergaenzt.
* Karten-Neuladen erhaelt gespeicherte Ziele und Terminmenue-Einstellungen.

= 0.7.3 =
* Karteneditor zeigt das gespeicherte öffentliche Klickziel je Gemeinde und markiert ungespeicherte Änderungen.
* Gewählter Kartentyp bestimmt das Klickverhalten in Karte und OV-Listen; fehlende oder ungültige Ziele erzeugen keinen Schein-Link.
* Kartendaten bleiben nach Theme-Updates in der WordPress-Datenbank erhalten. Mehrere Gemeinden können einem OV zugeordnet werden.

= 0.7.2 =
* Sponsoring, externe Compliance-Werbung sowie zugehöriges Badge, Widget und Editor-Block entfernt.

= 0.7.1 =
* Gemeinden ohne Website erhalten eine erreichbare Informationsseite mit Kontaktmöglichkeit.
* Explizite externe Kartenziele, OV-Liste und Dialog nutzen konsistente Navigation; Platzhalter werden verworfen.
* Eingebettete Personenlisten berücksichtigen automatisch den umgebenden OV.
* Bestehende OV-Rollen erhalten fehlende Upload-Rechte beim Update nachgetragen.
* Inhaltsvorlagen und Gestaltungshilfe sind ohne globale Designrechte erreichbar.

= 0.7.0 =
* Rollen- und Bereichsprüfung für Redaktion und Medien; geschützter Papierkorb.
* Datensparsame Rollenübersicht und kontrollierte Medienzuordnung mit Rücknahme.
* Lokale und externe OV-Websites sowie Datenschutz-/Impressumsrückfall korrigiert.
* Einheitliche Karten- und Listenziele, bestehende /ov-xy/-Routen und mobile Tastaturbedienung.
* Eindeutige Terminzuordnung und robuster iCal-Export.
* Newsletteranfrage mit ausdrücklicher Einwilligung, ohne automatisches Abonnement.
* Deutsches Geschäftsstelle-Handbuch und erweiterte Regressionstests.
* Release validiert alle Versionsangaben und lädt keine fehlende Demo-Datei.

= 0.5.1 =
* Optionaler Dark Mode (abschaltbar im Customizer)
* Theme-Credit (neurg.de) im Footer
* Filter-Button :visited-Fix
* minimal_title-Sanitization fuer Neue-Energie-Einstellungen

= 0.4.0 =
* Erstes oeffentliches Pre-Release
* Vollstaendiges Theme mit Personenverwaltung, Events, OV-System
* Eingebautes SEO, Kontaktformular, Cookie-Consent
* Interaktive Kreiskarte
* Spenden-Integration
* PHPUnit-Testsetup und Seed-Data fuer Entwicklung

== Credits ==

Inspiriert von "Joseph knows best" (v2.0.4) von Benjamin Jopen (kre8tiv.de)
und der Weiterentwicklung von Andreas Gregor (andreasgregor.de).
Vollstaendig neu aufgebaut von Severin Kistner (neurg.de).
