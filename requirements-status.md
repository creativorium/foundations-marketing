# Requested behavior: implementation and remaining work

This records scope, not a claim that every customer design is ready for sale.

| Requirement | Current behavior | Remaining work |
|---|---|---|
| Sell packages containing a chosen theme | WooCommerce checkout records the package, chosen published design and extras | Live payment intentionally deferred; real order-to-handover acceptance still needed |
| Browse designs and preview them | Compiled published masters populate the catalogue; draft demos require administrator access | Onyx remains a draft; no catalogue of 12 completed designs exists |
| Always-active keycard | Project keycard survives export and installation; staff record deployment domain/date | Domain moves are not reported automatically; no remote deactivation |
| Edit for a customer, then download | Isolated customer pages/settings, scoped theme/plugin/content archive, inbound content export | Onyx customer install/edit/export passed; code updates remain developer work, not an automatic merge |
| Include required plugins | Generated Foundations Site runtime is included | Arbitrary third-party dependencies are not collected or licensed automatically |
| Better contact/discovery form | Marketing plugin has guarded submits, retained answers on error, retry identity and stricter booking confirmation | External delivery remains untested; Onyx's separate enquiry form still needs its endpoint |
| Smooth page transitions | Short cover/reveal with reduced-motion and history handling | Production network/device review and measured performance remain necessary |
| AI team rules | Tracked workflow requires preview, editor save/reload, clean install and release evidence | Contributors must supply actual evidence for each design |

Onyx visual corrections were compared against the supplied standalone HTML, not inferred
from build success. See its template README and `delivery-verification.md`. Neither a
passing preview nor a package build replaces customer-install acceptance.
