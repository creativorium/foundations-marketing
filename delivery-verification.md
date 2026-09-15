# Delivery verification

Local acceptance run: 15 September 2026. This verifies the internal three-page fixture,
not the future catalogue of 12 designs. Marketing and customer WordPress remain separate.

## Passed

| Check | Evidence |
|---|---|
| PHP and build | 76 PHP files linted, 19 block-linter regression tests passed, 9 block-markup files checked, Vite builds passed |
| Scoped packaging | Separate theme/plugin/content ZIPs, checksums, source revision and dirty-worktree flag; custom fixture editor JS and frontend CSS compiled |
| Master to customer project | Independent page/media IDs; edits, navigation, typed image/page references and image alternative text survive export/import |
| Fresh customer content import | Imported the generated content ZIP through the WordPress admin on the fixture; earlier sample pages preserved as drafts |
| Customer Gutenberg | All three current pages saved and reloaded; 30 blocks valid, including the generated plugin's dynamic fixture block |
| Responsive customer pages | Home, About and Contact at 375, 820 and 1440 pixels: HTTP 200, one main/H1, no overflow, broken content images, unresolved tokens or marketing-site links |
| Customer settings and role | Site Owner can save Site Settings; Site Editor and plugin installation return 403 |
| Customer export back to staff | Export downloaded through Tools, imported into the matching project, settings recovered, a new release built and downloaded |
| Preview parity | Internal preview loads the custom block's compiled CSS and remapped image/link; screenshot inspected |
| Privacy | Anonymous customer previews and project REST reads denied; anonymous release download denied |
| Keycard | Visible on installed site; retained through export/release; installation domain recorded manually |
| Checkout | Hook now loads after WooCommerce; builder settings/defaults read correctly; duplicate extras removed; repeated total calculations remain stable |
| Contact backend | Saved enquiry and idempotent retry, invalid-input rejection, password exclusion, calendar failure and confirmed booking tested with all mail/HTTP/integrations intercepted |
| Contact frontend | Double submit sends one request, failure preserves answers, retry uses the same request ID, success clears the form, invalid discovery step cannot advance |
| Transitions | Normal navigation has the short cover; reduced-motion navigation bypasses it; back navigation clears the curtain |

Private raw logs and admin screenshots are in gitignored `doc/fixture-audit/`. The
reproducible responsive test writes screenshots/results to `artifacts/browser/`.
System Chrome stalled during early tests; the completed browser checks use the matching
Chromium supplied by Playwright 1.60.0.

## Reproduce

Run `npm run build`, `npx playwright install chromium`, and `npm run test:transition`.
Run `npm run test:browser -- http://foundations-fixture.local / /about/ /contact/` for
responsive checks. Run `npm run package -- base-three-page` for a test delivery archive.

The CLI integration scripts accept a Local WordPress `wp-load.php` path:

- `scripts/test-delivery-wordpress.php` also takes the generated delivery ZIP. It requires explicit `FM_CREATE_ACCEPTANCE_FIXTURE=1` because it creates a new acceptance project. Routine browser checks reuse existing records.
- `scripts/test-contact-wordpress.php` intercepts mail, HTTP and scheduled integrations
  and cleans up its test entries.
- `scripts/test-checkout-wordpress.php` uses a cloned existing product and does not
  create an order or change catalogue prices.

On Windows Local, pass the site's actual MySQL port using
`php -d mysqli.default_port=PORT ...`. Do not run the mutation tests on production.

## Limits still requiring real-design acceptance

Onyx is now present as a draft design with launch items still outstanding. Each real design needs its own
multi-page content, screenshots, editable-field tests, package install and browser review.
This run did not process a live payment, deliver an email, create an external appointment,
or deploy to a remote host. It did not measure production PageSpeed, audit all shell
variants, or test every supported WordPress/PHP version.

Exports cover selected pages, referenced media and Site Settings. They are not full
database backups or automatic upgrades. Additional third-party plugins and Site Editor
template overrides are not silently included. Keycards never disable a site, but they
also do not automatically report an unrecorded domain move. See `delivery-operations.md`.

## Onyx follow-up: 16 September 2026

The existing Onyx draft passed 15 responsive preview checks (five pages at three widths)
and five real Gutenberg save/reload checks, with no browser JavaScript errors. Its
generated package compiled successfully. The customer runtime now filters settings
metadata; a generated-runtime check verified only the three declared Onyx extensions.
The current build passed 87 PHP files, 19 linter tests and 16 markup files.

Three older fixture masters and four older acceptance projects were archived locally,
without deleting their pages, media or releases. One current fixture master and project
remain in the default Delivery list alongside Onyx. The mutation harness now requires
explicit opt-in; browser checks create no sample records.

Onyx customer-site installation remains unverified: the fixture database did not respond
on its configured Local port. Placeholder photography, missing font files, an unwired
enquiry form and release-content review remain launch requirements. No live payment,
external message or appointment was sent.
