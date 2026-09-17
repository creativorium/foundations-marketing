/**
 * Editor UI for foundations/onyx-page-hero.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, lead, note, layout } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Page opening', 'foundations')}>
          <TextControl
            label={__('Kicker', 'foundations')}
            value={eyebrow}
            onChange={(v) => setAttributes({ eyebrow: v })}
          />
          <TextareaControl
            label={__('Heading', 'foundations')}
            help={__('This is the page H1. Only one per page.', 'foundations')}
            value={heading}
            rows={3}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Standfirst', 'foundations')}
            value={lead}
            rows={4}
            onChange={(v) => setAttributes({ lead: v })}
          />
          <TextareaControl
            label={__('Boxed note', 'foundations')}
            help={__('An aside in a hairline box — used on the privacy page.', 'foundations')}
            value={note}
            rows={3}
            onChange={(v) => setAttributes({ note: v })}
          />
          <SelectControl
            label={__('Layout', 'foundations')}
            value={layout}
            options={[
              { label: __('Stacked', 'foundations'), value: 'stacked' },
              { label: __('Heading beside the standfirst', 'foundations'), value: 'split' },
            ]}
            onChange={(v) => setAttributes({ layout: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
