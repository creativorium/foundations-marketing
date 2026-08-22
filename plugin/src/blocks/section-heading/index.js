/**
 * Registration for foundations/section-heading.
 *
 * The block is server-rendered (`save: () => null`), so the database stores only the
 * attributes — markup always comes from render.php. That is what lets a front-end
 * contributor change the markup without a content migration.
 */
import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.jsx';
import './editor.scss';

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: () => null,
});
