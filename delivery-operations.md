# Delivery operations

This workflow sells a service package containing a chosen design. The intended catalogue
has 12 master designs; that does not mean 12 designs have been implemented or tested.
Payment processing is outside this implementation.

## Build and preview a master

1. Follow `team-template-workflow.md` and create `plugin/src/templates/<slug>/` using
   the multi-page structure in `customer-runtime.md`.
2. Run `npm run build`, then `npm run package -- <slug>`. Node runs at build time only.
3. Install `plugin-delivery/` as `foundations-delivery` on the internal marketing
   WordPress after `npm run build:delivery` has populated its runtime directory.
4. In **Delivery**, upload the generated `<slug>-delivery.zip`. It starts as a draft.
5. Preview every page, edit/save/reload in Gutenberg, and test a fresh customer install.
   Publish the demo only when those checks pass. Fixtures cannot be published.

The downloadable archive contains `theme.zip`, `plugin.zip`, `content.zip`, a checksum
manifest, and installation instructions. It is an outer delivery archive, not a theme
ZIP to upload directly into Appearance. Private release storage sits outside the public
WordPress directory; hosting must allow PHP to write there. PHP ZIP is required.

## A customer project

Create a project from a master, enter the customer reference, optional order ID and
requested changes. Its pages, media and settings are independent copies. Edit through
the project's links and use **Preview customer site** to review the complete result.
Build a downloadable release when ready; each release has an ID and SHA-256 checksum.
An order made through the supported WooCommerce checkout hooks also creates a project
for a published master design. Creating a project does not prove payment was received.

Install the inner theme and plugin ZIPs on the customer's fresh WordPress, activate
both, then import `content.zip` under **Tools → Foundations Delivery**. Imports refuse
existing page slugs and refuse a second starter import to protect existing edits.
Record the installed domain and release on the internal project. Keycards are delivery
identifiers, never activation gates. There is no remote heartbeat or automatic tracking
of an unreported domain move. No customer identity is embedded in the distributed card.

Use a Site Owner account for customer editing: it can edit content and Site Settings,
but cannot switch themes, install plugins or use the Site Editor. Staff retain admin
access. Assign that role deliberately; activation does not downgrade existing users.

## Later edits

Routine checks reuse the existing fixture and Onyx records. Superseded local acceptance
records may be marked with `_fm_delivery_archived`; Delivery hides these by default and
offers **Show archived test records**. Archiving preserves pages, media and releases.
Remove that metadata to restore a record to the default list. Never archive customer
work merely because its design name matches a fixture.

Staff can export edited content on a customer site and import it into its internal
project, then build another release. Code updates do not automatically merge with
customer content. Existing releases stay immutable. Test a fresh import before handover.
The exporter includes explicitly selected pages, referenced uploads (including image
alternative text), and Site Settings. Select additional pages on the export screen.
This is not a database backup: users, orders, third-party options and Site Editor
template customizations are not exported.

For an unpublished or published master that has no customer projects, rebuild with the
same stable slug and upload it with **Replace an existing master with the same package
slug** selected. Delivery validates and imports the replacement first, keeps the master
record, catalogue URL and visibility, then removes the superseded demo content and files.
The replacement refreshes the demo and future customer packages; it does not update a
customer website that was already installed.

Replacement is refused as soon as a customer project depends on the master. Use a
versioned design slug in that case and review it as a new master. Existing releases remain
available. Automatic upgrades or content merges into customer projects are not
implemented; plan and test those migrations separately.

Third-party plugins are not silently bundled. Any dependency needs explicit licensing,
version and installation instructions before that design is released. The customer
runtime and the marketing contact plugin are separate; customer-specific form adapters
must be implemented and tested by the template team.
