import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit, { Save } from './edit.jsx';
import './editor.scss';

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: Save,
});
