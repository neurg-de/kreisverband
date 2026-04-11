.PHONY: up down restart logs shell db-shell wp-install wp-activate seed setup zip release clean css css-watch test phpcs

# ── Development Environment ──────────────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

db-shell:
	docker compose exec db mariadb -u wordpress -pwordpress wordpress

# ── WordPress Setup ──────────────────────────────────────────────────────────

wp-install:
	docker compose exec wordpress bash -c '\
		curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
		chmod +x wp-cli.phar && \
		php wp-cli.phar core install \
			--url=http://localhost:8080 \
			--title="GRÜNE Musterkreis" \
			--admin_user=admin \
			--admin_password=admin \
			--admin_email=admin@example.com \
			--locale=de_DE \
			--allow-root && \
		rm wp-cli.phar'

wp-activate:
	docker compose exec wordpress bash -c '\
		curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
		chmod +x wp-cli.phar && \
		php wp-cli.phar theme activate neurg-kreisverband --allow-root && \
		rm wp-cli.phar'

# ── Seed Data ────────────────────────────────────────────────────────────────

seed:
	docker compose exec wordpress bash -c '\
		curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
		chmod +x wp-cli.phar && \
		php wp-cli.phar eval "gk_seed_all();" --allow-root && \
		php wp-cli.phar rewrite flush --allow-root && \
		rm wp-cli.phar'
	@echo "Seed data created. Visit http://localhost:8080"

# ── Full Setup (install + activate + seed) ───────────────────────────────────

setup: up
	@echo "Waiting for containers..."
	@sleep 5
	@$(MAKE) wp-install
	@$(MAKE) wp-activate
	@$(MAKE) seed
	@echo "Done! Visit http://localhost:8080 (admin/admin)"

# ── Tests ────────────────────────────────────────────────────────────────────

test:
	composer exec phpunit

phpcs:
	composer exec phpcs -- --standard=phpcs.xml.dist theme/

# ── CSS Build ────────────────────────────────────────────────────────────

css:
	npx sass theme/lib/scss:theme/lib/css --style=compressed --no-source-map

css-watch:
	npx sass --watch theme/lib/scss:theme/lib/css --style=expanded

# ── Release ──────────────────────────────────────────────────────────────────

VERSION := $(shell grep 'Version:' theme/style.css | head -1 | sed 's/.*Version: *//')

zip:
	@bin/build-zip.sh

release:
	@test -n "$(V)" || (echo "Usage: make release V=patch  (or minor, major, 1.2.3)" && exit 1)
	@bin/release.sh $(V)

# ── Cleanup ──────────────────────────────────────────────────────────────────

clean:
	docker compose down -v
	rm -f neurg-kreisverband-*.zip
