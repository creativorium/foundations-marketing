# Contact and discovery forms

Tracked copy of the owner's existing `fm-contact-discovery` plugin. Install this folder
under that same plugin directory name; preserve existing WordPress options and entries.
Do not activate a second copy under `plugin-contact` alongside the original.

Version 1.9.2 consolidates submission into the existing frontend bundle, gives retries a
stable request ID, avoids a booking request when no appointment is selected, preserves
answers on error, and rejects false calendar confirmations. It retains the existing
jQuery-based discovery form pending a separately tested migration.

Calendar tests must stub HTTP and mail. A saved enquiry is distinct from a confirmed
appointment; the calendar service must report success before confirmation mail is sent.
This is the marketing site's form plugin, not an implicit dependency of every design.
