.PHONY: up down restart logs shell db-shell wp-install wp-activate zip clean css css-watch

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
	docker exec -it theme-dev-wordpress-1 bash

db-shell:
	docker exec -it theme-dev-db-1 mariadb -u wordpress -pwordpress wordpress

# ── WordPress Setup ──────────────────────────────────────────────────────────

wp-install:
	docker exec theme-dev-wordpress-1 bash -c '\
		curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
		chmod +x wp-cli.phar && \
		php wp-cli.phar core install \
			--url=http://localhost:8080 \
			--title="Gruene Kreisverband" \
			--admin_user=admin \
			--admin_password=admin \
			--admin_email=admin@example.com \
			--locale=de_DE \
			--allow-root && \
		rm wp-cli.phar'

wp-activate:
	docker exec theme-dev-wordpress-1 bash -c '\
		curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
		chmod +x wp-cli.phar && \
		php wp-cli.phar theme activate gruene-kreisverband --allow-root && \
		rm wp-cli.phar'



# ── CSS Build ────────────────────────────────────────────────────────────

css:
	npx sass theme/lib/scss:theme/lib/css --style=compressed --no-source-map

css-watch:
	npx sass --watch theme/lib/scss:theme/lib/css --style=expanded

# ── Release ──────────────────────────────────────────────────────────────────

VERSION := $(shell grep 'Version:' theme/style.css | head -1 | sed 's/.*Version: *//')

zip:
	@echo "Building release zip for version $(VERSION)..."
	@cd theme && zip -r ../gruene-kreisverband-$(VERSION).zip . \
		-x '*.DS_Store' -x '__MACOSX/*'
	@echo "Created gruene-kreisverband-$(VERSION).zip"

# ── Cleanup ──────────────────────────────────────────────────────────────────

clean:
	docker compose down -v
	rm -f gruene-kreisverband-*.zip
