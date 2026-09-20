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
bin/install-wp-tests.sh wordpress_test root '' localhost 6.9.4

# Run PHPUnit tests
make test

# Run PHP CodeSniffer
make phpcs

# JavaScript syntax and compiled CSS / installable ZIP
make js-check
npm install
npm run build
make zip
```

Use a separate disposable database: PHPUnit recreates its WordPress tables.
CI uses PHP 8.3 and WordPress 6.9.4. The installer reuses the matching
`wp-phpunit/wp-phpunit` Composer test library when present. The bootstrap finds
the installed PHPUnit polyfills automatically.

PHPCS checks PHP and the compatible source assets. Three generated minified Sass
outputs are checked through the Sass build. The map generator's modern JavaScript
is excluded from PHPCS 3 because its tokenizer corrupts template literals and
modern operators; `make js-check` checks every JavaScript file, and browser smoke
tests cover the generator and map behavior. The parser-only `Internal.NoCodeFound`
warning is excluded for the static HTML kitchen-sink partials; those files
intentionally contain no PHP. All actual coding/security rules remain enabled.

`bin/smoke-local.py` exercises authenticated WordPress REST requests against an
isolated loopback demo site with synthetic accounts and `gk_seed_all()` data.
Set `GK_SMOKE_URL`, `GK_SMOKE_PASSWORD` and `GK_SMOKE_ADMIN_PASSWORD` in the local
environment; never commit credentials. It creates synthetic posts/events and
checks publishing, scope boundaries, trash, restore and protected admin pages.
Newsletter browser tests additionally require a local `pre_wp_mail` interceptor
so that no actual messages leave the test installation.

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

1. Follow the `dev` → squash `main` branch workflow in README.md.
2. Update all four version fields: `theme/style.css`, `theme/functions.php`
   (`GK_VERSION`), `theme/readme.txt` and `package.json`; add the changelog.
3. Run the checks above, independently review the changes, and test the ZIP as a
   regular update in a separate staging installation with backup/restore.
4. Push the release commit to `main`, verify its CI checks, then push the matching
   `vX.Y.Z` tag. GitHub Actions publishes the ZIP and `SHA256SUMS` automatically.
5. Download the actual release assets and verify their checksum. Local and CI
   ZIPs can differ in timestamps; use the checksum of the downloaded asset.
6. Install manually on the target site after its own backup and staging check.
   Publishing a GitHub release does not update a WordPress installation.

See [the 0.7.0 validation and deployment report](docs/RELEASE-0.7.0.md) and
[the editorial handbook](docs/BENUTZERHANDBUCH.md).

## Credits

- Inspired by "Joseph knows best" by Benjamin Jopen (kre8tiv.de)
  and the work of Andreas Gregor (andreasgregor.de)
- Full rewrite by Severin Kistner (neurg.de)
