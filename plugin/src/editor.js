/**
 * Editor bundle — registers every block's editor side.
 *
 * Adding a component: create plugin/src/blocks/<name>/, then add one import here and
 * one @use in styles/blocks.scss. The PHP side discovers blocks by scanning for
 * block.json, so nothing else changes. Keep both lists alphabetical.
 */
import './blocks/package-builder';
import './blocks/addons';
import './blocks/audience';
import './blocks/benefits';
import './blocks/cta';
import './blocks/faq';
import './blocks/feature-cards';
import './blocks/hero';
import './blocks/marquee';
import './blocks/page-hero';
import './blocks/photo-banner';
import './blocks/photo-strip';
import './blocks/pricing';
import './blocks/quotes';
import './blocks/section-heading';
import './blocks/split-list';
import './blocks/steps';
import './blocks/template-grid';
import './blocks/template-library';

/*
 * Onyx Aesthetics — a SOLD TEMPLATE's own blocks, not this website's (how-to-work.md
 * §2.1b). They are imported here only so the template can be built and previewed on our
 * catalogue site; the packager compiles them into the customer's own plugin from
 * plugin/src/templates/onyx/ and never ships this bundle.
 */
import './templates/onyx/blocks/contact';
import './templates/onyx/blocks/cta';
import './templates/onyx/blocks/feature';
import './templates/onyx/blocks/hero';
import './templates/onyx/blocks/menu';
import './templates/onyx/blocks/page-hero';
import './templates/onyx/blocks/pillars';
import './templates/onyx/blocks/policy';
import './templates/onyx/blocks/reviews';
import './templates/onyx/blocks/steps';
import './templates/onyx/blocks/story';

import './templates/meridian-modern/blocks/hero';
import './templates/meridian-modern/blocks/credentials';
import './templates/meridian-modern/blocks/story';
import './templates/meridian-modern/blocks/conditions';
import './templates/meridian-modern/blocks/steps';
import './templates/meridian-modern/blocks/reviews';
import './templates/meridian-modern/blocks/faq';
import './templates/meridian-modern/blocks/booking';
import './templates/meridian-modern/blocks/policy';
import './templates/meridian-modern/blocks/page-hero';
import './templates/meridian-modern/blocks/contact';
import './templates/meridian-modern/blocks/site-header';
import './templates/meridian-modern/blocks/site-footer';
