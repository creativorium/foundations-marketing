import { registerBlockType } from '@wordpress/blocks';
import Editor from './Editor.jsx';

/** Every Quartz block is server-rendered: attributes only, no saved markup. */
export default function register(metadata, fields = {}) {
  registerBlockType(metadata.name, {
    ...metadata,
    edit: props => <Editor {...props} metadata={metadata} fields={fields} />,
    save: () => null,
  });
}
