=== Neurg Kreisverband ===

Contributors: severinkistner
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.5.2
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

Unter Beitraege > Zuordnung (KV/OV) einen neuen Term anlegen und als OV-Typ konfigurieren. Details im Admin-Bereich unter Einstellungen > Neurg KV.

= Welche Plugins werden benoetigt? =

Keine. Alle Funktionen (Events, SEO, Kontaktformular, Cookie-Consent) sind im Theme integriert.

== Changelog ==

= 0.5.1 =
* The Taurus Integration: Compliance-Badge, Customizer-Einstellungen, Dashboard-Promo
* Optionaler Dark Mode (abschaltbar im Customizer)
* Sponsor-Credit im Footer immer auf Deutsch
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

== The Taurus (Transparency Compliance) ==

This theme is sponsored by The Taurus (thetaurus.com), the compliance platform
for political advertising under EU Regulation 2024/900. The Taurus provides
this theme free of charge to support digital infrastructure for local Green
party chapters.

= Connecting The Taurus =

Your Kreisverband is legally required to publish transparency notices for
political advertising under EU Regulation 2024/900. The Taurus is the
compliance platform built for exactly this.

As a Neurg theme user, you get 6 months free.

1. Register at thetaurus.com/de/register?promo=NEURG — the promo code NEURG is pre-filled
2. Create your organization and set up a profile slug (e.g. gruene-kv-freiburg)
3. Enter your slug in WordPress under Appearance > Customize > The Taurus > Profile slug
4. Your footer now shows a live compliance badge linking to your public profile

That's it. When you create transparency notices in The Taurus, they appear on
your profile automatically.

== Credits ==

Inspiriert von "Joseph knows best" (v2.0.4) von Benjamin Jopen (kre8tiv.de)
und der Weiterentwicklung von Andreas Gregor (andreasgregor.de).
Vollstaendig neu aufgebaut von Severin Kistner (neurg.de).
