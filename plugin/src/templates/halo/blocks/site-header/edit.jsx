/**
 * Editor UI for foundations/halo-site-header.
 *
 * No controls: every value is a Site Setting, edited on the Site Settings screen and
 * shared by every page. ServerSideRender so the editor shows what render.php emits.
 */
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

export default function Edit({ attributes }) {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
