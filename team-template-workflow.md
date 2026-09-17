# Team template workflow — proposed shared rules

This tracked file defines acceptance evidence for humans and AI assistants building master designs and customer projects. Read it together with how-to-work.md and customer-runtime.md. Confidential client material stays in gitignored doc/ or the private delivery manager.

## Before work

Read how-to-work.md, customer-runtime.md and the task brief. State the design slug, task type (master design, customer customization, shared runtime fix), allowed paths, source design/asset location, editable fields, target pages and acceptance criteria. Use your own checkout/worktree and local WordPress database. Preserve uncommitted work. Follow existing account, branch, review and merge permissions; never infer permission to publish or contact customers from a design task.

## Master designs versus customer copies

A master design is reusable, versioned source. A customer project is an isolated copy with its own content, media and settings. Never put customer edits or keycards into the master design. Never put order/customer records, secrets, database exports or live credentials in Git or a distributable template.

## HTML delivery to editable WordPress

1. Read the HTML and assets; inventory pages, sections, states and responsive layouts.
2. Define editable text, images, links, repeaters, settings and which structure remains fixed.
3. Build scoped blocks and theme overrides using the existing token/runtime contracts.
4. Compose multi-page block content and metadata. Keep header/footer in parts.
5. Use stable import references, not source-site IDs or development URLs.
   Attachment and internal-page ID fields must be named `id`, end in `Id`, or end in
   `_id` (for example `heroImageId` or `contactPageId`). Navigation uses `page`.
   Keep repeaters as objects with those named fields; bare arrays of IDs are not
   portable. Export/import tests must exercise every image and internal link field.
6. Keep each template's assets independently buildable; declare any runtime dependency explicitly.

## Preview before requesting review

Install the tested browser once with `npx playwright install chromium`. After running
`npm run build`, use `npm run test:browser -- http://your-fixture.local / /about/ /contact/`
(substitute the design's actual routes). It saves screenshots and results in gitignored
`artifacts/browser/`. Use Playwright's bundled Chromium, not a mismatched system Chrome.
This smoke test is read-only; it does not replace the Gutenberg or round-trip checks below.

Install/build the design on your own test WordPress site. Open every page in a browser and Gutenberg. Save, reload, and verify no invalid blocks. Check desktop 1440px, tablet 820px and mobile 375px; check keyboard navigation and reduced motion where applicable. Verify links, images, header/footer, empty content, long copy and interactive states.

A contributor's local URL is useful to that contributor but is not a reviewer-accessible preview. Provide an approved shared staging preview or an installable preview artifact with reproducible setup instructions. When shared demo routing is unavailable, use a dedicated preview site; do not call unviewed code visually verified. Never overwrite another contributor's preview.

## Pull request evidence

Include source revision, brief description, master slug/version, changed scope, editable fields, actual preview location, screenshots at the three widths, commands and results, Gutenberg save/reload evidence, unresolved issues and dependency changes. Run npm run build and relevant tests. Label unrun checks honestly. New validation rules need positive and negative regression cases.

One coherent template PR can contain that template's private blocks, parts and content. Shared runtime/form/transition changes belong in focused separate PRs. Contributors request owner review and do not merge their own work.

## Releases and updates

For an HTML delivery, render the supplied HTML and the WordPress preview at the same
desktop viewport. Compare every page's header/footer, typography, widths, spacing,
section layout and content; include side-by-side screenshots in review evidence.
Passing overflow or block-validation checks does not prove visual parity. Record
intentional mobile/accessibility differences. Use the supplied fonts/assets when
available instead of quietly substituting system fonts or a different shared shell.

A preview is not a delivered release. Release approval requires an independent clean install of the scoped code plus content/media/settings, verification that development domains are absent, and editable Gutenberg content after import. Customer customization additionally requires export and re-import of the edited project.

Record source revision and base/design/runtime/content versions. Preserve existing customer content during code updates; test compatibility before deploying. Never silently replace customer content with new starter content. Keycard or reporting failures never disable a delivered site.

## Testing external integrations

Form tests must use stubbed services or explicitly configured test destinations. Verify success, validation errors, timeouts, stale responses and retry behavior without sending real emails, creating bookings or triggering webhooks unless explicitly authorized.
