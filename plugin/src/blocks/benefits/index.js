/**
 * Registration for foundations/benefits.
 * Server-rendered: the database stores attributes only.
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
