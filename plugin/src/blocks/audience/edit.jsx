/**
 * Editor UI. The block reads its own layout from render.php, so the editor previews it
 * with ServerSideRender rather than keeping a second copy of the markup in JS that
 * would drift out of step with the PHP.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import Controls from './controls.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <Controls attributes={attributes} setAttributes={setAttributes} />
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
