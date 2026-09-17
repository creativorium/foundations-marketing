/**
 * Editor UI for foundations/onyx-reviews.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, meta, items } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Section intro', 'foundations')}>
          <TextControl
            label={__('Eyebrow', 'foundations')}
            value={eyebrow}
            onChange={(v) => setAttributes({ eyebrow: v })}
          />
          <TextControl
            label={__('Heading', 'foundations')}
            value={heading}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextControl
            label={__('Aggregate line', 'foundations')}
            help={__('Sits beside the heading — for example "4.9 average · 340 verified reviews".', 'foundations')}
            value={meta}
            onChange={(v) => setAttributes({ meta: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ quote: '', who: '', rating: '' }}
          label={(item, i) => item.who || __('Review', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add review', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextareaControl
                label={__('Quote', 'foundations')}
                value={item.quote || ''}
                rows={4}
                onChange={(v) => update({ quote: v })}
              />
              <TextControl
                label={__('Attribution', 'foundations')}
                value={item.who || ''}
                onChange={(v) => update({ who: v })}
              />
              <TextControl
                label={__('Rating, in words', 'foundations')}
                help={__(
                  'Read aloud instead of the stars, which are decorative. For example "Rated 5 out of 5". Leave empty to hide the stars.',
                  'foundations'
                )}
                value={item.rating || ''}
                onChange={(v) => update({ rating: v })}
              />
            </>
          )}
        </Repeater>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
