# Contact and discovery forms

Tracked copy of the owner's existing `fm-contact-discovery` plugin. Install this folder
under that same plugin directory name; preserve existing WordPress options and entries.
Do not activate a second copy under `plugin-contact` alongside the original.

Version 1.9.4 adds the native `[fm_discovery_page]` page layout and updates the
five-step discovery UI to the current Foundations visual system. Version 1.9.3 applies the current Foundations Marketing visual system to the contact
form without allowing its styles to leak into the surrounding page. Version 1.9.2
consolidated submission into the existing frontend bundle, gives retries a
stable request ID, avoids a booking request when no appointment is selected, preserves
answers on error, and rejects false calendar confirmations. It retains the existing
jQuery-based discovery form pending a separately tested migration.

Calendar tests must stub HTTP and mail. A saved enquiry is distinct from a confirmed
appointment; the calendar service must report success before confirmation mail is sent.
This is the marketing site's form plugin, not an implicit dependency of every design.
