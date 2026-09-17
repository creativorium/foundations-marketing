/**
 * Editor UI for foundations/onyx-cta.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Notice, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { heading, lead, ctaLabel, ctaUrl } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Call to action', 'foundations')}>
          <TextareaControl
            label={__('Heading', 'foundations')}
            value={heading}
            rows={3}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Lead', 'foundations')}
            value={lead}
            rows={3}
            onChange={(v) => setAttributes({ lead: v })}
          />
          <TextControl
            label={__('Button text', 'foundations')}
            value={ctaLabel}
            onChange={(v) => setAttributes({ ctaLabel: v })}
          />
          <TextControl
            label={__('Button link', 'foundations')}
            value={ctaUrl}
            onChange={(v) => setAttributes({ ctaUrl: v })}
          />

          {ctaLabel.trim() !== '' && ctaUrl.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('The button has text but no destination, so it is not rendered.', 'foundations')}
            </Notice>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
