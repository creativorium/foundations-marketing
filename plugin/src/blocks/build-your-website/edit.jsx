/**
 * Editor UI for foundations/build-your-website.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import Controls from './controls.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps({ className: 'fm-build-your-website' });

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
