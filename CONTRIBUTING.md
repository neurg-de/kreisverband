# Contributing

## Development Setup

```bash
# Full setup: start containers, install WP, activate theme, seed demo data
make setup

# Or step by step:
make up              # Start Docker containers
make wp-install      # Install WordPress (German locale)
make wp-activate     # Activate the theme
make seed            # Create demo content (pages, posts, persons, events, menus)
```

WordPress runs at **http://localhost:8080** (admin: `admin` / `admin`).
phpMyAdmin runs at **http://localhost:8081**.

The theme directory (`theme/`) is mounted live into the container — changes are reflected immediately.

## Testing

```bash
# Install PHP dependencies (first time)
composer install

# Install WordPress test suite (first time)
bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run PHPUnit tests
make test

# Run PHP CodeSniffer
make phpcs
```

## Project Structure

```
docker-compose.yml      # Local dev environment (WordPress + MariaDB + phpMyAdmin)
Makefile                # Common dev commands
composer.json           # PHP dependencies (PHPUnit, PHPCS)
phpunit.xml.dist        # PHPUnit configuration
phpcs.xml.dist          # PHPCS configuration
tests/                  # PHPUnit tests
  bootstrap.php         # Test bootstrap (loads WP test suite + theme)
  ThemeSetupTest.php    # Theme support, menus, sidebars
  PostTypesTest.php     # Custom post types & taxonomies
  EventsTest.php        # Event system
  ShortcodesTest.php    # Shortcode registration & output
bin/
  install-wp-tests.sh   # WordPress test suite installer
theme/                  # The WordPress theme (this is what gets released)
  style.css             # Theme metadata
  readme.txt            # WordPress readme
  functions.php         # Entry point — includes modular files from inc/
  inc/
    post-types.php      # Custom post types: person; taxonomies: abteilung, gk_zuordnung
    meta-boxes.php      # Admin meta boxes for person & page data
    abteilung-meta.php  # Per-abteilung meta (position, function, hidden)
    shortcodes.php      # [vorstand], [personenliste], [gliederungen], etc.
    ortsverband.php     # OV system: config, routing, navigation
    events.php          # Event CPT, iCal export, shortcodes
    theme-setup.php     # Menus, scripts, sidebars
    roles.php           # Custom roles: Kreisadmin, OV-Admin, OV-Autor
    settings.php        # Admin settings page
    setup-wizard.php    # First-run configuration wizard
    seo.php             # Built-in SEO (Open Graph, JSON-LD)
    donation.php        # Donation system (Twingle, bank details)
    contact-form.php    # Built-in contact form
    cookie-consent.php  # Cookie consent banner (DSGVO)
    social-links.php    # Social media platform registry
    social-share.php    # Social share buttons
    blocks.php          # Gutenberg blocks
    admin.php           # Admin customizations
    widgets.php         # Custom widgets
    kreiskarte.php      # Interactive SVG map
    kreiskarte-generator.php  # Map generator from OSM data
    dev-seed-data.php   # Dev: seed demo content
  template-parts/       # Reusable content templates
  lib/                  # CSS, JS, images, fonts
```

## Releases

Releases follow semantic versioning. To create a release:

1. Update the version in `theme/style.css` and `theme/functions.php` (`GK_VERSION`)
2. Commit and tag: `git tag v0.4.0`
3. Push: `git push origin main --tags`
4. GitHub Actions builds the release zip automatically

## Credits

- Inspired by "Joseph knows best" by Benjamin Jopen (kre8tiv.de)
  and the work of Andreas Gregor (andreasgregor.de)
- Full rewrite by Severin Kistner (neurg.de)
