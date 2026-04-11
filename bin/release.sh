#!/usr/bin/env bash
set -euo pipefail

# ── Release script for neurg-kreisverband ───────────────────────────────────
#
# Usage:
#   bin/release.sh major          # 0.5.0 → 1.0.0
#   bin/release.sh minor          # 0.5.0 → 0.6.0
#   bin/release.sh patch          # 0.5.0 → 0.5.1
#   bin/release.sh 1.2.3          # explicit version
#
# This script:
#   1. Reads the current version, calculates the next one
#   2. Bumps the version in all 4 source files
#   3. Builds the zip to verify the release is valid
#   4. Commits the version bump on the current branch
#   5. Merges into main
#   6. Tags with v<version>
#   7. Pushes main + tag (triggering CI release)
#   8. Switches back to the original branch

MAIN_BRANCH="main"

# ── Parse arguments ─────────────────────────────────────────────────────────

if [[ $# -ne 1 ]]; then
    echo "Usage: bin/release.sh <major|minor|patch|X.Y.Z>" >&2
    echo "" >&2
    echo "Examples:" >&2
    echo "  bin/release.sh patch    # bump patch version" >&2
    echo "  bin/release.sh minor    # bump minor version" >&2
    echo "  bin/release.sh major    # bump major version" >&2
    echo "  bin/release.sh 1.2.3    # set explicit version" >&2
    exit 1
fi

INPUT="$1"

# ── Read current version ───────────────────────────────────────────────────

if [[ ! -f "theme/style.css" ]]; then
    echo "ERROR: Run this script from the repository root." >&2
    exit 1
fi

OLD_VERSION=$(sed -n 's/^Version: *//p' theme/style.css | tr -d '[:space:]')
if [[ -z "$OLD_VERSION" ]]; then
    echo "ERROR: Could not read current version from theme/style.css" >&2
    exit 1
fi

# Normalize current version to semver (X.Y → X.Y.0)
IFS='.' read -r CUR_MAJOR CUR_MINOR CUR_PATCH <<< "$OLD_VERSION"
CUR_MAJOR="${CUR_MAJOR:-0}"
CUR_MINOR="${CUR_MINOR:-0}"
CUR_PATCH="${CUR_PATCH:-0}"

# ── Calculate new version ──────────────────────────────────────────────────

case "$INPUT" in
    major)
        NEW_VERSION="$((CUR_MAJOR + 1)).0.0"
        RELEASE_TYPE="major"
        ;;
    minor)
        NEW_VERSION="${CUR_MAJOR}.$((CUR_MINOR + 1)).0"
        RELEASE_TYPE="minor"
        ;;
    patch)
        NEW_VERSION="${CUR_MAJOR}.${CUR_MINOR}.$((CUR_PATCH + 1))"
        RELEASE_TYPE="patch"
        ;;
    *)
        # Explicit version — strip leading 'v' if present
        NEW_VERSION="${INPUT#v}"

        # Normalize to semver (X.Y → X.Y.0)
        IFS='.' read -r NEW_MAJOR NEW_MINOR NEW_PATCH <<< "$NEW_VERSION"
        NEW_MAJOR="${NEW_MAJOR:-0}"
        NEW_MINOR="${NEW_MINOR:-0}"
        NEW_PATCH="${NEW_PATCH:-0}"
        NEW_VERSION="${NEW_MAJOR}.${NEW_MINOR}.${NEW_PATCH}"

        # Validate format
        if ! [[ "$NEW_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            echo "ERROR: Invalid version '$INPUT'. Use major, minor, patch, or X.Y.Z" >&2
            exit 1
        fi

        # Determine release type from comparison
        if [[ "$NEW_MAJOR" -gt "$CUR_MAJOR" ]]; then
            RELEASE_TYPE="major"
        elif [[ "$NEW_MINOR" -gt "$CUR_MINOR" ]]; then
            RELEASE_TYPE="minor"
        elif [[ "$NEW_PATCH" -gt "$CUR_PATCH" ]]; then
            RELEASE_TYPE="patch"
        else
            RELEASE_TYPE="release"
        fi
        ;;
esac

TAG_NAME="v${NEW_VERSION}"

# ── Pre-flight checks ──────────────────────────────────────────────────────

if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "ERROR: Working tree has uncommitted changes. Commit or stash them first." >&2
    exit 1
fi

if git rev-parse "$TAG_NAME" >/dev/null 2>&1; then
    echo "ERROR: Tag '$TAG_NAME' already exists. Delete it first if you want to re-release:" >&2
    echo "  git tag -d $TAG_NAME && git push origin :refs/tags/$TAG_NAME" >&2
    exit 1
fi

# Normalize old version for file replacements (use as-is from style.css)
# but ensure the new version is always full semver
if [[ "$OLD_VERSION" == "$NEW_VERSION" ]]; then
    echo "ERROR: Version is already $NEW_VERSION — nothing to do." >&2
    exit 1
fi

CURRENT_BRANCH=$(git branch --show-current)

echo ""
echo "  $RELEASE_TYPE release: $OLD_VERSION → $NEW_VERSION (tag: $TAG_NAME)"
echo ""

# ── Run tests ─────────────────────────────────────────────────────────────

echo "  Running tests..."
if ! composer exec phpunit 2>&1; then
    echo "" >&2
    echo "ERROR: Tests failed — aborting release." >&2
    exit 1
fi
echo "  ✓ All tests passed"
echo ""

# ── Bump version in all files ──────────────────────────────────────────────

echo "  Bumping version in source files..."

# 1. theme/style.css — "Version: X.Y.Z"
sed -i '' "s/^Version: *${OLD_VERSION}/Version: ${NEW_VERSION}/" theme/style.css

# 2. theme/functions.php — "define( 'GK_VERSION', 'X.Y.Z' );"
sed -i '' "s/GK_VERSION', *'${OLD_VERSION}'/GK_VERSION', '${NEW_VERSION}'/" theme/functions.php

# 3. package.json — "version": "X.Y.Z"
sed -i '' "s/\"version\": *\"${OLD_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" package.json

# 4. theme/readme.txt — "Stable tag: X.Y.Z"
sed -i '' "s/Stable tag: *${OLD_VERSION}/Stable tag: ${NEW_VERSION}/" theme/readme.txt

# ── Verify all files were updated ──────────────────────────────────────────

ERRORS=0

STYLE_V=$(sed -n 's/^Version: *//p' theme/style.css | tr -d '[:space:]')
FUNC_V=$(sed -n "s/.*GK_VERSION', *'\\([^']*\\)'.*/\\1/p" theme/functions.php)
PKG_V=$(grep '"version"' package.json | sed 's/.*": *"//;s/".*//')
README_V=$(sed -n 's/^Stable tag: *//p' theme/readme.txt | tr -d '[:space:]')

for file_version in "style.css:$STYLE_V" "functions.php:$FUNC_V" "package.json:$PKG_V" "readme.txt:$README_V"; do
    file="${file_version%%:*}"
    ver="${file_version#*:}"
    if [[ "$ver" != "$NEW_VERSION" ]]; then
        echo "  ERROR: $file still shows '$ver', expected '$NEW_VERSION'" >&2
        ERRORS=$((ERRORS + 1))
    fi
done

if [[ $ERRORS -gt 0 ]]; then
    echo "Version bump failed. Restoring files..." >&2
    git checkout -- theme/style.css theme/functions.php package.json theme/readme.txt
    exit 1
fi

echo "  ✓ All 4 files updated to $NEW_VERSION"

# ── Build zip to verify the release is valid ───────────────────────────────

echo ""
echo "  Building zip to verify release..."
bin/build-zip.sh
echo ""

# ── Commit version bump ────────────────────────────────────────────────────

echo "  Committing version bump..."
git add theme/style.css theme/functions.php package.json theme/readme.txt
git commit -m "Bump version to ${NEW_VERSION}"

# ── Push current branch ────────────────────────────────────────────────────

echo "  Pushing $CURRENT_BRANCH..."
git push origin "$CURRENT_BRANCH"

# ── Merge into main ────────────────────────────────────────────────────────

echo "  Merging $CURRENT_BRANCH → $MAIN_BRANCH..."
git checkout "$MAIN_BRANCH"
git pull origin "$MAIN_BRANCH"
git merge "$CURRENT_BRANCH" --no-edit
git push origin "$MAIN_BRANCH"

# ── Tag and push ───────────────────────────────────────────────────────────

echo "  Tagging $TAG_NAME..."
git tag -a "$TAG_NAME" -m "Release ${NEW_VERSION}"
git push origin "$TAG_NAME"

# ── Switch back ────────────────────────────────────────────────────────────

echo "  Switching back to $CURRENT_BRANCH..."
git checkout "$CURRENT_BRANCH"

# ── Done ───────────────────────────────────────────────────────────────────

echo ""
echo "  ✓ Released $TAG_NAME ($RELEASE_TYPE)"
echo "  ✓ CI will build and attach neurg-kreisverband-${NEW_VERSION}.zip"
echo ""
