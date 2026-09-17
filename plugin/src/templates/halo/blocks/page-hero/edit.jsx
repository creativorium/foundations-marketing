/**
 * Editor UI for foundations/halo-page-hero. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, lead, note } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Page opening', 'foundations')}>
          <TextControl
            label={__('Eyebrow', 'foundations')}
            value={eyebrow}
            onChange={(v) => setAttributes({ eyebrow: v })}
          />
          <TextControl
            label={__('Heading', 'foundations')}
            help={__('This is the page H1. Only one per page.', 'foundations')}
            value={heading}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Standfirst', 'foundations')}
            value={lead}
            rows={4}
            onChange={(v) => setAttributes({ lead: v })}
          />
          <TextareaControl
            label={__('Note to the editor', 'foundations')}
            help={__(
              'Addressed to whoever is filling this page in, not to its readers. Clear it once the page is finished.',
              'foundations'
            )}
            value={note}
            rows={4}
            onChange={(v) => setAttributes({ note: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
