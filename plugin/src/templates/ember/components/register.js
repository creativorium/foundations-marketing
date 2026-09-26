import { registerBlockType } from '@wordpress/blocks';

export default function register(metadata, edit) {
  registerBlockType(metadata.name, { ...metadata, edit, save: () => null });
}
