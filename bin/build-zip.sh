#!/usr/bin/env bash
set -euo pipefail

THEME_SLUG="neurg-kreisverband"
THEME_DIR="theme"
DISTIGNORE=".distignore"

# ── Extract version from style.css ───────────────────────────────────────────

VERSION=$(sed -n 's/^Version: *//p' "$THEME_DIR/style.css" | tr -d '[:space:]')
if [[ -z "$VERSION" ]]; then
    echo "ERROR: Could not read Version from $THEME_DIR/style.css" >&2
    exit 1
fi

FUNCTIONS_VERSION=$(sed -n "s/.*GK_VERSION', *'\\([^']*\\)'.*/\\1/p" "$THEME_DIR/functions.php")
if [[ "$VERSION" != "$FUNCTIONS_VERSION" ]]; then
    echo "ERROR: Version mismatch — style.css says '$VERSION', functions.php says '$FUNCTIONS_VERSION'" >&2
    exit 1
fi

ZIP_NAME="${THEME_SLUG}-${VERSION}.zip"

echo "Building $ZIP_NAME ..."

# ── Build CSS ────────────────────────────────────────────────────────────────

echo "  Compiling SCSS..."
npx sass "$THEME_DIR/lib/scss:$THEME_DIR/lib/css" --style=compressed --no-source-map

# ── Assemble temp directory ──────────────────────────────────────────────────

BUILD_DIR=$(mktemp -d)
trap 'rm -rf "$BUILD_DIR"' EXIT

cp -r "$THEME_DIR" "$BUILD_DIR/$THEME_SLUG"

# ── Strip files from .distignore ─────────────────────────────────────────────

if [[ -f "$DISTIGNORE" ]]; then
    while IFS= read -r pattern; do
        pattern=$(echo "$pattern" | sed 's/#.*//' | xargs)
        [[ -z "$pattern" ]] && continue
        find "$BUILD_DIR/$THEME_SLUG" -name "$pattern" -exec rm -rf {} + 2>/dev/null || true
    done < "$DISTIGNORE"
fi

# Always remove dev-only files
rm -rf "$BUILD_DIR/$THEME_SLUG/inc/dev-seed-data.php"

# ── Validate theme structure ─────────────────────────────────────────────────

ERRORS=0

if [[ ! -f "$BUILD_DIR/$THEME_SLUG/style.css" ]]; then
    echo "ERROR: style.css missing from build" >&2
    ERRORS=$((ERRORS + 1))
fi

if [[ ! -f "$BUILD_DIR/$THEME_SLUG/functions.php" ]]; then
    echo "ERROR: functions.php missing from build" >&2
    ERRORS=$((ERRORS + 1))
fi

if [[ ! -f "$BUILD_DIR/$THEME_SLUG/index.php" ]]; then
    echo "ERROR: index.php missing from build" >&2
    ERRORS=$((ERRORS + 1))
fi

if ! grep -q "Theme Name:" "$BUILD_DIR/$THEME_SLUG/style.css"; then
    echo "ERROR: style.css missing 'Theme Name:' header" >&2
    ERRORS=$((ERRORS + 1))
fi

# Check no dev files leaked in
for devfile in .git .github docker-compose.yml Makefile phpunit.xml.dist composer.json tests node_modules vendor; do
    if [[ -e "$BUILD_DIR/$THEME_SLUG/$devfile" ]]; then
        echo "ERROR: Dev file '$devfile' found in build — check .distignore" >&2
        ERRORS=$((ERRORS + 1))
    fi
done

if [[ $ERRORS -gt 0 ]]; then
    echo "Build failed with $ERRORS error(s)." >&2
    exit 1
fi

# ── Create zip ───────────────────────────────────────────────────────────────

rm -f "$ZIP_NAME"
(cd "$BUILD_DIR" && zip -rq "$OLDPWD/$ZIP_NAME" "$THEME_SLUG" -x '*.DS_Store' -x '__MACOSX/*')

# ── Verify zip structure ─────────────────────────────────────────────────────

ZIP_LISTING=$(zipinfo -1 "$ZIP_NAME")

FIRST_ENTRY=$(echo "$ZIP_LISTING" | head -1)
if [[ "$FIRST_ENTRY" != "${THEME_SLUG}/" ]]; then
    echo "ERROR: Zip root is '$FIRST_ENTRY', expected '${THEME_SLUG}/'" >&2
    rm -f "$ZIP_NAME"
    exit 1
fi

FLAT_FILES=$(echo "$ZIP_LISTING" | grep -cv "^${THEME_SLUG}/" || true)
if [[ "$FLAT_FILES" -gt 0 ]]; then
    echo "ERROR: Zip contains $FLAT_FILES file(s) outside '${THEME_SLUG}/' directory" >&2
    rm -f "$ZIP_NAME"
    exit 1
fi

SIZE=$(du -h "$ZIP_NAME" | cut -f1)
FILE_COUNT=$(echo "$ZIP_LISTING" | wc -l | tr -d ' ')

echo ""
echo "  ✓ Version:   $VERSION"
echo "  ✓ Structure:  $THEME_SLUG/ (wrapper directory present)"
echo "  ✓ Files:      $FILE_COUNT"
echo "  ✓ Size:       $SIZE"
echo "  ✓ Output:     $ZIP_NAME"
echo ""
echo "Ready to upload to WordPress."
