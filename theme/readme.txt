=== Neurg Kreisverband ===

Contributors: severinkistner
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress-Theme fuer GRUENE Kreisverband- und Ortsverband-Websites.

== Description ==

Neurg Kreisverband ist ein spezialisiertes, kostenloses WordPress-Theme fuer GRUENE Kreisverband- und Ortsverband-Websites. Entwickelt und gepflegt unter [neurg.de](https://neurg.de).

Funktionen:

* Personendatenbank mit Abteilungen und Zuordnungen
* Interaktive SVG-Kreiskarte (OpenStreetMap-basiert)
* Ortsverband-Verwaltung mit eigenem Routing und Templates
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

= Wie richte ich einen Ortsverband ein? =

Als Administrator unter Verband einen Ortsverband anlegen und eine lokale Startseite oder eine externe Kontakt-Website hinterlegen. Anschliessend die Links in OV-Liste und Kreiskarte pruefen. Das vollstaendige deutsche Benutzerhandbuch liegt im Repository unter docs/BENUTZERHANDBUCH.md.

= Welche Plugins werden benoetigt? =

Keine. Alle Funktionen (Events, SEO, Kontaktformular, Cookie-Consent) sind im Theme integriert.

== Changelog ==

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
