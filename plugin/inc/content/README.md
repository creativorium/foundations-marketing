# Approved marketing page copy

`faq.json` and `how-it-works.json` contain the copy extracted from the approved
FAQ and How It Works HTML deliveries in the owner's Notes folder. These are
marketing pages, not customer master templates. Their server-rendered shortcodes
use the existing site theme and Vite styles; the design-canvas runtime is not shipped.

The FAQ follows the current £299 website offer: five working days after the
completed form and content arrive. Retired Root/Grow/Rise package comparisons
have been removed, as requested by the owner on 23 September 2026.

How It Works bundles the approved laptop and founders photos as optimised WebP
assets in `plugin/assets/images/marketing/`. No manual media upload is required.
The FAQ source contains no photos.

Pending documents: the owner will supply the revision policy and checklist later.
Set the `fm_revision_policy_url` and `fm_content_checklist_url` WordPress options to
their approved URLs when available. Until then the policy is plain text and the
checklist button is disabled with an “Available soon” message. Do not invent a URL.

Local review:
- `/frequently-asked-questions/`
- `/how-it-works/`

The How It Works migration creates or updates its page once and repoints existing
FAQ/How It Works items in assigned menus. It does not rerun the homepage photo migration.
