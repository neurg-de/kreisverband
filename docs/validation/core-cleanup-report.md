# Core module PHPCS cleanup and security review fix

Task task_79fccd9bcc28 / dispatch ctx_31085992ee78. No commits or live changes.

## Verified PHP result

Initial assigned-file scan: 319 errors and 17 warnings after the isolated roles.php PHPCBF pass. All 15 assigned PHP modules plus the existing four role/media helpers now pass the unchanged WordPress/WPCS ruleset with zero errors or warnings. Command and exact final output are in `docs/validation/phpcs-core-final.txt`.

Files: blocks.php, widgets.php, theme-setup.php, kreiskarte-generator.php, settings.php, dev-seed-data.php, abteilung-meta.php, seo.php, meta-boxes.php, social-share.php, post-types.php, social-links.php, taurus.php, donation.php, roles.php; independent review also required a behavioral fix to role-security.php.

Most changes add callback/parameter documentation, complete comments, use full ternaries/Yoda comparisons and fix formatting. Existing public PHP parameter names and include paths are retained. Short ternary expansion only repeats variables/properties, never re-executes a function. Role author filtering now compares explicitly normalized integer IDs. Seed dates use wp_date for WordPress timezone semantics.

## Real security/behavior fixes

- Private read boundary: reproduced reviewer's foreign-scope private authored post leak with a failing RolesTest regression before the fix. The read_post exception now applies only to is_post_publicly_viewable posts without a password, with media always scoped. Private/draft/pending/future reads are denied at capability and REST boundaries; public frontend reads remain available and REST edit context remains denied.
- Demo seed admin triggers now require an action-specific WordPress nonce in addition to manage_options, before data changes. Direct WP-CLI `gk_seed_all()` and `make seed` remain unchanged. Developer admin URLs must use `wp_nonce_url( admin_url( '?gk_seed=1' ), 'gk_seed' )`; social-only action uses `gk_seed_social`.
- Map admin script data is decoded and re-encoded with JSON_HEX_TAG/AMP/APOS/QUOT, preventing stored JSON strings from closing the inline script. AJAX JSON input validates scalar JSON and recursively sanitizes nested text while preserving numeric geometry. Malformed municipality/mapping rows are skipped. Reads and writes use WP_Filesystem with the theme directory context and relaxed ownership; inability to write returns the existing explicit error instead of silently claiming success.
- Contact metadata saves unslash input, validates the public email address, and retains shared nonce/edit_post authorization. Department metadata ignores malformed rows and sanitizes field content and department keys. Legacy scope meta-box callback now checks edit_post as well as nonce.
- Output values escape at their final HTML/attribute/textarea contexts. Removed early textarea escaping to avoid turning ampersands into doubly escaped entities. Widget field IDs/names and dynamic social class attributes are escaped; buffered social share HTML passes wp_kses_post.

## Narrow documented exclusions

No blanket exclusions and no PHPCS config changes were made by this worker. Intentional local exceptions identify the exact source and reason:

- Existing widgets.php/taurus.php class-module filename and adjacent structure rules: preserve public include paths and combined legacy registration modules. These exceptions are limited to the relevant filename/declaration lines.
- Registered sidebar before/after widget/title wrappers: trusted HTML from WordPress/theme registration, not user-submitted content, retained verbatim for layout compatibility.
- Public `$default` / `$echo` parameter names: preserve compatibility for PHP named-argument callers.
- Unused arguments on existing public core hook callbacks: preserve the published callback signatures.
- Dynamic metadata input: shared gk_can_save_meta gate already performs nonce and edit_post checks before the exact flagged operations.
- Nested department/map inputs: their dedicated downstream sanitizer checks every field/string; raw complex structures are not stored directly.
- Department block tax_query: intentional taxonomy boundary for the OV-aware editor picker.

## Tests

Added tests/CoreSecurityTest.php (six regressions): authorized/unauthorized contact save, invalid email, apostrophe unslashing, malformed department metadata, JSON decoding and geometry preservation, actual WP_Filesystem write/read of a temporary file, seed CSRF rejection before mutation, and single escaping of textarea content.

Expanded tests/RolesTest.php for independent review's nonpublic read leak. Baseline: one assertion failed for private foreign authored content. After fix: private/draft/pending/future capability and REST reads denied, public read allowed, REST edit context denied.

Final full run: `docker exec -w /workspace kreisverband-tests flock /tmp/gk-phpunit.lock vendor/bin/phpunit` => **91 tests, 364 assertions, all passing**. Exact output also retained at `/tmp/gk-core-phpunit-final.txt`. Owned-file `git diff --check` passes, including corrected PHPCBF trailing whitespace on the map-generator template.

## JavaScript follow-up

Coordinator expanded scope to block-abteilung.js and kreiskarte-generator.js. The department block was formatted, documented, and its immutable options length cached for iteration; Node syntax validation passes.

PHPCS 3's JavaScript tokenizer corrupts modern generator syntax: it splits nullish/optional-chain/exponent operators, rewrites template-literal HTML/interpolation, drops return-object braces and oscillates between arrow-function indentation rules. All corrupting experimental formatter changes to the generator were discarded using the pre-edit snapshot; its behavior is unchanged, with only file documentation added, and `node --check` passes. Coordinator explicitly selected preserving modern JavaScript with Node validation and will add a precise generator-only PHPCS exception plus CI/Makefile JavaScript syntax checks. No syntax downgrades or inline exclusions remain in the generator; its only change is file documentation. Final PHP + department JS PHPCS scan reports 20/20 files and exit 0; both node --check commands pass. Generator behavior smoke covers six scenarios (geometry, SVG escaping, preview/back controls, AJAX POST action/nonce/JSON, save failure, empty data) with mocked DOM/network: all pass; exact evidence is docs/validation/generator-smoke.txt and reproducible harness /tmp/gk-generator-smoke.cjs. This is a Node VM behavior smoke, not a browser or live-site test.
