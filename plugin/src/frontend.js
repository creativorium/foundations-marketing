/**
 * Front-end bundle for the blocks: their compiled styles, plus the small amount of
 * vanilla behaviour a block genuinely needs. No framework ships to the browser.
 */
import './styles/blocks.scss';

import initTemplateLibrary from './blocks/template-library/filter.js';

initTemplateLibrary();
