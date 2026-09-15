import metadata from './block.json';
import { registerBlockType } from '@wordpress/blocks';
import { createElement as el } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
registerBlockType(metadata.name, {
  ...metadata,
  edit: ({ attributes, setAttributes }) => el('div', useBlockProps(),
    el(TextControl, {label:'Fixture text',value:attributes.label,onChange:label=>setAttributes({label})})),
  save: () => null,
});
