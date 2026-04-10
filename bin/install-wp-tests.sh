#!/usr/bin/env bash
#
# Install WordPress test suite for PHPUnit.
#
# Usage: bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-db]
#
# Adapted from the official WP-CLI scaffold.

if [ $# -lt 3 ]; then
    echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-db]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo "$TMPDIR" | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
    if [ "$(which curl)" ]; then
        curl -s "$1" > "$2"
    elif [ "$(which wget)" ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [ "$WP_VERSION" = "latest" ]; then
    WP_VERSION=$(download https://api.wordpress.org/core/version-check/1.7/ - | grep -o '"version":"[^"]*"' | head -1 | sed 's/"version":"//;s/"//')
    if [ -z "$WP_VERSION" ]; then
        echo "Could not determine latest WP version."
        exit 1
    fi
fi

WP_TESTS_TAG="tags/$WP_VERSION"

set -ex

install_wp() {
    if [ -d "$WP_CORE_DIR" ]; then
        return
    fi

    mkdir -p "$WP_CORE_DIR"

    if [ "$WP_VERSION" = "trunk" ]; then
        local ARCHIVE_URL="https://wordpress.org/nightly-builds/wordpress-latest.zip"
    else
        local ARCHIVE_URL="https://wordpress.org/wordpress-$WP_VERSION.zip"
    fi

    download "$ARCHIVE_URL" /tmp/wordpress.zip
    unzip -q /tmp/wordpress.zip -d /tmp
    mv /tmp/wordpress/* "$WP_CORE_DIR"
}

install_test_suite() {
    # Portable in-place sed
    local ioption='-i'
    if [[ $(uname -s) == 'Darwin' ]]; then
        ioption='-i.bak'
    fi

    if [ ! -d "$WP_TESTS_DIR" ]; then
        mkdir -p "$WP_TESTS_DIR"
        svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
        svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/" "$WP_TESTS_DIR/data"
    fi

    if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
        download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
        sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
        sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"
        rm -f "$WP_TESTS_DIR/wp-tests-config.php.bak"
    fi
}

install_db() {
    if [ "$SKIP_DB_CREATE" = "true" ]; then
        return 0
    fi

    # Parse DB_HOST for port or socket.
    local EXTRA=""
    if echo "$DB_HOST" | grep -qe ':'; then
        local DB_HOSTNAME=$(echo "$DB_HOST" | cut -d: -f1)
        local DB_SOCK_OR_PORT=$(echo "$DB_HOST" | cut -d: -f2)
        if echo "$DB_SOCK_OR_PORT" | grep -qe '^[0-9]\{1,\}$'; then
            EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
        elif [ -S "$DB_SOCK_OR_PORT" ]; then
            EXTRA=" --socket=$DB_SOCK_OR_PORT"
        fi
    else
        EXTRA=" --host=$DB_HOST"
    fi

    mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS"$EXTRA 2>/dev/null || true
}

install_wp
install_test_suite
install_db
