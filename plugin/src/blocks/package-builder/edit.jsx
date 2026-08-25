/**
 * Editor UI. Previewed with ServerSideRender so the editor shows the same markup the
 * front end will, rather than a second copy in JS that drifts out of step with the PHP.
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
