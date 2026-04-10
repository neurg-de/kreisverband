# Contributing

## Development Setup

```bash
# Start the local WordPress environment
make up

# First time: install WordPress (German locale)
make wp-install

# Activate the theme
make wp-activate
```

WordPress runs at **http://localhost:8080** (admin: `admin` / `admin`).
phpMyAdmin runs at **http://localhost:8081**.

The theme directory (`theme/`) is mounted live into the container — changes are reflected immediately.

## Project Structure

```
docker-compose.yml      # Local dev environment (WordPress + MariaDB + phpMyAdmin)
Makefile                # Common dev commands
theme/                  # The WordPress theme (this is what gets released)
  functions.php         # Entry point — includes modular files from inc/
  inc/
    post-types.php      # Custom post types: person, gliederung
    meta-boxes.php      # Admin meta boxes for person/gliederung data
    shortcodes.php      # [vorstand], [personenliste], [gliederungen], etc.
    ortsverband.php     # OV system: config, routing, navigation
    theme-setup.php     # Menus, scripts, sidebars
    admin.php           # Security, editor, role restrictions
    widgets.php         # Social media + teaser widgets
  template-parts/       # Reusable content templates
  lib/                  # CSS, JS, images, fonts
```

## Releases

Releases follow semantic versioning. To create a release:

1. Update the version in `theme/style.css`
2. Commit and tag: `git tag v0.1.0`
3. Push: `git push origin main --tags`
4. GitHub Actions builds the release zip automatically

## Credits

- Original theme "Joseph knows best" by Benjamin Jopen (kre8tiv.de)
- Modified by Andreas Gregor (andreasgregor.de)
- Extracted and developed by Severin Kistner
