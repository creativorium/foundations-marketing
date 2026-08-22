/**
 * Editor bundle — registers every block's editor side.
 *
 * Adding a component: create plugin/src/blocks/<name>/, then add one import here and
 * one @use in styles/blocks.scss. The PHP side discovers blocks by scanning for
 * block.json, so nothing else changes. Keep both lists alphabetical.
 */
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
