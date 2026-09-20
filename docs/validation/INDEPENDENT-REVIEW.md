# Independent release review — 0.7.0

Reviewer dispatch: `task_d3a0ba7c4e19` / `ctx_7dd75b2b5d9a`. Reviewed against `origin/dev` and `/Users/m1/Documents/ORCA_KREISVERBAND_TASK.md`, including the handbook, tests and release workflows. All five reproduced implementation findings have been fixed and independently reverified, including the final coordinator freeze comparison. No implementation edits, commits, production changes or external mail were made.

## Release decision

**APPROVE the reviewed 0.7.0 source and package for release, after the documented CI gate. No remaining release-blocking findings.** The initial recommendation was to block release: extra real WordPress probes found defects despite the original 81-test suite passing. The final maintained suite now passes 97 tests / 413 assertions, all independent probes pass, and native browser checks pass. This is source/package review, not approval of an untested production installation.

## Findings

### IR-01 — High: private content remains readable outside an OV author's assigned area

- File: `theme/inc/role-security.php:117`, `gk_scope_meta_cap`.
- The exemption described as keeping published content public actually exempts every nonattachment `read_post` check, including private content.
- Reproduction: create an `ov-review-a` taxonomy term and matching `gk_ovautor`; create a private post authored by that user but assigned to Kreisverband (also representative of content reassigned by administration); authenticate as that OV user and request `GET /wp/v2/posts/<id>`.
- Actual: `edit_post` is false, but REST returns **200** with the private title and full content. Expected: forbidden for private foreign-area content, while genuinely public content stays public.
- Independent probe: `IndependentReviewTest::test_private_foreign_content_read`, expected 403 / actual 200. Fix must cover object read capability and practical direct/REST access, with a positive public-content control.
- Fixed: only genuinely public, unprotected nonattachment content bypasses scope. The original private read now returns 403; public foreign and own private positive controls remain 200.

### IR-02 — High: OV deletion handler trusts a broad capability and nonce after role reduction

- File: `theme/inc/admin.php:363` (`gk_ortsverband_handle_delete`), especially its `gk_manage_ov` gate and `wp_delete_term` call (line numbers may move during PHPCS cleanup).
- `gk_manage_ov` is held by OV admins and the office role; the handler does not require administrator authority or an object-specific taxonomy deletion capability. Hiding the deletion screen does not provide this check.
- Reproduction: as a synthetic administrator obtain a valid `gk_ov_delete` nonce; change that same account to `gk_ovadmin` without changing its session; submit the nonce and the ID of a *different* OV to the admin deletion handler. Nonces remain valid for the same user/session when roles change.
- Actual: foreign OV term is permanently deleted, despite the current account being restricted; relationships and term settings are removed. New trash guards may stop foreign posts being trashed but do not protect the taxonomy term. Expected: refuse before any mutation unless the current user has administrator-level deletion authority.
- Independent probe: `IndependentReviewTest::test_downgraded_ov_admin_cannot_delete_foreign_term`; `get_term` becomes null. The probe intercepts redirect with an exception to inspect postconditions; it does not bypass the nonce/capability gates. A never-admin OV account was not demonstrated obtaining this nonce; the proven scenario is a retained nonce after role reduction, and the missing authorization gate is real.
- Fixed: the handler now requires `manage_options` as well as the OV capability and nonce. The retained-nonce/role-reduction reproduction now preserves the foreign term. This missing gate predated parts of the release, but fell directly within the requested review.

### IR-03 — Medium: contact/newsletter accepts an unreadable password-protected privacy page

- File: `theme/inc/contact-form.php:28–35`, `gk_contact_privacy_url`.
- Reproduction: configure the WordPress privacy option with a published page protected by a password, leave the KV privacy selection empty, then render `[newsletter_anfrage]` or `[kontaktformular]` anonymously.
- Actual: helper returns the protected page URL and enables submission; the visitor cannot read the notice without its password. Footer/cookie legal resolution correctly uses `gk_public_page_id` and rejects the same page, so behavior is inconsistent.
- Independent probe: `IndependentReviewTest::test_password_protected_privacy_blocks_form`, expected empty URL / actual `http://example.org/?page_id=5`.
- Expected: shared public-page validation, with fallback to another valid public privacy page or disabled form.
- Fixed: form privacy resolution uses `gk_public_page_id`; the protected-page reproduction now resolves no usable privacy URL.

### IR-04 — High: OV homepage image settings bypass media scope

- File: `theme/inc/admin.php:319`, image IDs in `gk_ortsverband_handle_save`.
- Reproduction: authenticate as an OV-admin, obtain the normal own-OV save nonce and submit `ov_hp[hero_image]` / `ov_hp[candidate_image]` containing a KV or other OV attachment ID.
- Actual: both IDs are accepted with only `absint`; the corresponding images render in `page-OV.php`. `gk_object_in_scope` correctly reports false for the same image, but this settings path bypasses that boundary.
- Independent real-handler probe: `test_ov_homepage_cannot_select_foreign_media` persisted the foreign image ID. Expected: reject new foreign selections while preserving existing legacy references unchanged.
- Fixed: the handler validates each new hero/portrait selection as an in-scope attachment image, preserves unchanged legacy references per field, and gives a visible rejection notice. The forged selection reproduction now leaves the previous image unchanged; maintained tests also cover allowed own-scope images.

### IR-05 — Medium: OV homepage image buttons have no working media picker

- File: `theme/inc/admin.php:130` (media enqueue gate), `:520` (handler emitted only inside main settings page), `:714` and `:774` (buttons on separate edit page).
- Reproduction: open Verband as an OV-admin and click “Bild wählen” under Hero-Bild or candidate portrait. The edit form emits buttons but not the JavaScript handler; its media enqueue also requires the global theme-options capability that this release correctly removes from OV-admin.
- Static inspection plus render probe `test_ov_edit_form_has_media_button_handler`: edit HTML has `.gk-media-pick` but no `wp.media` registration; repository search found no alternate handler. Actual browser retest required after fix.
- Expected: shared handler and authorized media enqueue on the edit form, without restoring global theme-options rights.
- Browser confirmation: isolated profile as local `ov-neuburg` on `:8082/wp-admin/admin.php?page=gk-settings`; actual “Bild wählen” click left zero `.media-modal` dialogs and `typeof window.wp.media === "undefined"`. No shared profile cookies or account roles/passwords changed.
- Fixed: `gk_enqueue_settings_media` uses OV-management plus upload permission; `gk_render_settings_media_picker` is shared by the main and OV edit screens. A native browser click as OV-admin now opens the real media modal and its close control works, without changing global privileges or saving content.

## Final independent validation

All database-backed runs used `kreisverband-tests`, `/workspace`, and `flock /tmp/gk-phpunit.lock`; the original development site on port 8080 and production were not altered.

| Independent command/check | Result |
|---|---|
| `docker exec -w /workspace kreisverband-tests flock /tmp/gk-phpunit.lock vendor/bin/phpunit` | **97 tests / 413 assertions PASS**, final WordPress test library pinned to 6.9.4 |
| Same command with `/tmp/IndependentReviewTest.php` | **6 tests / 11 assertions PASS**; original IR-01–05 reproductions plus foreign direct trash/update control |
| Same command with `/tmp/IndependentMediaReviewTest.php` | **3 tests / 12 assertions PASS** |
| `docker exec -w /workspace kreisverband-tests vendor/bin/phpcs --standard=phpcs.xml.dist theme/` | exit 0, no findings |
| `make js-check` | exit 0 for all project JavaScript files |
| `bash -n bin/build-zip.sh bin/install-wp-tests.sh bin/release.sh` | exit 0 |
| `git diff --check` | exit 0 |
| ZIP CRC, wrapper, source-byte comparison and development-file exclusion | PASS; every packaged source file matched current source at inspection |

The independent media probes restore multiple original assignments, refuse rollback when an original term is missing, preserve a **real JPEG** legacy featured image through a REST resubmission, refuse a new foreign/unassigned replacement, and retain public foreign / own private readable content. Test source is temporary review code outside the repository; equivalent maintained regressions for the fixed issues now reside in the project tests.

Real authenticated HTTP requests returned **403** for the office account’s attempts to open role administration and media migration. An isolated OV-admin profile received **403** for those screens, Themes and Customizer. Local calendar responses had `text/calendar; charset=utf-8`; a real OV filter returned only its current synthetic events, while an invalid OV returned zero events.

The original suite result was **81 tests / 326 assertions PASS**. The initial independent four-probe run deliberately failed three secure expectations (IR-01–03); two further probes exposed IR-04–05. These were real controller/handler calls with valid synthetic authorization context, not mock-only assertions. All original failure expectations now pass after the fixes.

## Actual click, mobile and keyboard evidence

[Full native browser matrix](independent-browser-results.json), [focus and media-picker checks](independent-accessibility-results.json), and [independent direct HTTP status / title / H1 checks](independent-route-http.json) contain synthetic data only.

An independent **headless Google Chrome browser controlled by Playwright** loaded `http://localhost:8082/ov-linktest/`; desktop viewport 1440×1000, mobile viewport 390×844. It exercised all 14 configured municipalities in seven modes:

1. Desktop map: actual pointer click inside each polygon.
2. Desktop map: focus followed by native Enter.
3. Mobile list: actual pointer click.
4. Mobile list: native Enter.
5. Mobile map: actual polygon click, opened dialog and its real destination CTA.
6. Mobile map: native Enter, preserving native link navigation.
7. `[ortsverband_liste]`: native Enter on each named fixture link.

**98/98 checks passed:** 77 actual destination navigations for 11 available targets, plus 21 checks proving the three unavailable municipalities expose no actionable link. The final local and external fixture links navigated in the current page; obsolete map new-window flags no longer changed local navigation. Every available destination independently returned **HTTP 200**, and title/H1 identified the expected synthetic OV. No JavaScript page errors occurred.

| Municipality | Actual target | All seven modes |
|---|---|---|
| Wörthsee | `/ov-woerthsee/` | PASS |
| Inning am Ammersee | `/ov-inning/` | PASS |
| Herrsching am Ammersee | `/ov-herrsching/` | PASS |
| Andechs | `http://localhost:8083/external-ov/` | PASS |
| Seefeld | `No link / Im Aufbau` | PASS |
| Gilching | `/ov-gilching/` | PASS |
| Krailling | `/ov-krailling/` | PASS |
| Tutzing | `/ov-tutzing/` | PASS |
| Feldafing | `No link / Im Aufbau` | PASS |
| Pöcking | `/ov-poecking/` | PASS |
| Starnberg | `/ov-starnberg/` | PASS |
| Berg | `/ov-berg/` | PASS |
| Gauting | `/ov-gauting/` | PASS |
| Weßling | `No link / Im Aufbau` | PASS |

Gilching explicitly tested the legacy `/ov-gilching/` route **without** `_gk_homepage_id`. Herrsching tested a usable local OV overriding stale map `werbung` flags. Andechs used an external-origin fixture on port 8083; this proves external routing behavior, not the correctness of any production third-party address.

Five additional native checks passed: a physical tap on an inactive mobile list item leaves the URL unchanged; opening the mobile map dialog focuses the CTA; Tab and Shift+Tab remain within its controls; Escape closes it and returns focus to the activating polygon; and the repaired OV-admin media picker opens and closes a real WordPress media dialog without saving content.

Earlier Orca actual list clicks reached `/ov-landsberg/` and `/ov-musterstadt/`. Instrumentation showed Orca `keypress Enter` returning success without DOM keyboard events; a URL waiter then stalled that review tab. That tooling result was **not** counted as keyboard coverage. Only the reviewer’s stalled tab was closed, and native external Chrome supplied the successful keyboard evidence; coordinator tabs/cookies were not modified.

## Security, compatibility, documentation and release assessment

- Role upgrades preserve account role assignments; the office role remains `gk_kvautor_ov` and receives no administrator capabilities. Object scope applies to editing, protected reads, REST, media selection and reversible deletion. Core permanent-deletion filters prevent restricted users from bypassing the UI; configured automatic WordPress trash expiry remains an operational limit, documented in the handbook.
- Media preview is read-only by default. Applying a reviewed item requires administrator capability, an object nonce and unchanged preview hash; a durable before/after snapshot precedes assignment. Rollback refuses later changes or missing original terms. Files, attachment IDs and existing featured-image references are preserved. The older author-based migration excludes attachments.
- Contact/newsletter checks include exact consent values, a scalar reader, nonce, honeypot, a hashed-IP transient rate limit, form-target binding, duplicate-render suppression, fixed newsletter recipient and accessible mail failures. There is no theme subscription database or automatic mailing-list enrollment. No external test mail was sent. Legal-page verification establishes public navigation availability, not legal sufficiency of policy text.
- OV links, public-page validation, legacy `full` alias and slug homepage fallback were reviewed. iCal filters nonpublic/password-protected content, escapes text, converts the configured time zone to UTC, writes exclusive all-day end dates and folds UTF-8 safely. Existing tests and actual filtered feeds cover these paths.
- Reviewed the German handbook and `docs/RELEASE-0.7.0.md`; they distinguish editorial/admin responsibilities, newsletter interest/subscription, media review/rollback, synthetic staging/production and manual live installation. The stale blanket handbook claim that `werbung` always suppresses a usable local page was corrected by the coordinator and rechecked in the final handbook.
- Reviewed CI, `Makefile`, Composer pin, test bootstrap/download changes and ZIP script. CI now covers dev/main, PHPUnit, PHPCS, all-JavaScript syntax and ZIP integrity; version checks agree across all four version files. PHPCS exclusions are limited to three compiler outputs and the modern generator JS that PHPCS 3 cannot parse; JS syntax and generator behavior require their explicit checks. No broad new rule waiver was added.
- The tag publication workflow produces `SHA256SUMS`. It does not itself depend on the separate lint workflow, so the coordinator must wait for quality CI before pushing the release tag, as already required by the release process.
- Inspected coordinator evidence `stage-update-rollback.txt`: an actual regular ZIP installation on isolated staging reported 0.7.0, office role unchanged, role migration version 2 and OV global-options denied; subsequent database plus files restoration returned 0.6.2 and the previous migration state, with frontend/login smoke checks passing. This is reviewed coordinator evidence, not a claim that the reviewer independently performed the installation.

## Package and scope limits

Inspected local artifact: `neurg-kreisverband-0.7.0.zip`, SHA-256 `0eb1175cedfbaf1e8ad6e656864e37db4c4c26dd61a9066fc8bbbc3fc196c02f`. CRC and expected root directory passed; no vendor, node_modules, test, Git or demo-seed file leaked. Every packaged source file was byte-identical to the workspace version at inspection. The final GitHub-built ZIP can have a different hash due to build timestamps; use the checksum published with that artifact.

This review covers synthetic local data and the specified release changes. It does not replace the user’s production backup, representative staging copy, real external-address correction, mail-transport/consent process or manual live-install smoke tests. The reviewer did not publish, commit, install on production, or contact third parties.

## Freeze audit

Coordinator freeze received at 2026-09-20 14:44:53 UTC and reaffirmed at 14:45:56 UTC. The final `origin/dev` diff, README/CONTRIBUTING/handbook changes and package were reviewed again; all 163 previously hashed source/build files matched the reviewed snapshot. After freeze, the maintained suite again passed **97/413**, both independent probe files again passed **6/11** and **3/12**, the full native browser matrix again passed **98/98**, and PHPCS/JavaScript checks remained clean. ZIP wrapper and byte comparison remained correct with the SHA-256 recorded above. No implementation edits or commits were made by the reviewer; authored deliverables are this report and three synthetic JSON evidence files.
