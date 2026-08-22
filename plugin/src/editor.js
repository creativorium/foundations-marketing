/**
 * Editor bundle — registers every block's editor side.
 *
 * Adding a component: create plugin/src/blocks/<name>/, then add one import here and
 * one @use in styles/blocks.scss. The PHP side discovers blocks by scanning for
 * block.json, so nothing else changes. Keep both lists alphabetical.
 */
import './blocks/benefits';
import './blocks/cta';
import './blocks/faq';
import './blocks/hero';
import './blocks/marquee';
import './blocks/photo-strip';
import './blocks/pricing';
import './blocks/quotes';
import './blocks/section-heading';
import './blocks/steps';
import './blocks/template-grid';
