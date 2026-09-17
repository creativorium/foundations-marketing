/**
 * Editor UI for foundations/halo-packages. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

// A plan's features are plain strings, so they edit as lines rather than as a nested
// repeater inside a repeater — which is a worse tool than a text box for four bullets.
const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, lead, plans } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Heading', 'foundations')}>
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
          <TextareaControl
            label={__('Standfirst', 'foundations')}
            value={lead}
            rows={3}
            onChange={(v) => setAttributes({ lead: v })}
          />
        </PanelBody>

        <Repeater
          items={plans}
          onChange={(next) => setAttributes({ plans: next })}
          blank={{
            tier: '',
            name: '',
            price: '',
            unit: '',
            features: [],
            ctaLabel: '',
            ctaUrl: '',
            featured: false,
          }}
          label={(item, i) => item.name || __('Plan', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add plan', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Tier', 'foundations')}
                help={__('The small line above the name — "Starter", "Most chosen".', 'foundations')}
                value={item.tier || ''}
                onChange={(tier) => update({ tier })}
              />
              <TextControl
                label={__('Plan name', 'foundations')}
                value={item.name || ''}
                onChange={(name) => update({ name })}
              />
              <TextControl
                label={__('Price', 'foundations')}
                value={item.price || ''}
                onChange={(price) => update({ price })}
              />
              <TextControl
                label={__('Unit', 'foundations')}
                help={__('What the price buys — "/ month", "/ 3 sessions".', 'foundations')}
                value={item.unit || ''}
                onChange={(unit) => update({ unit })}
              />
              <TextareaControl
                label={__('What is included', 'foundations')}
                help={__('One per line.', 'foundations')}
                value={(item.features || []).join('\n')}
                rows={5}
                onChange={(v) => update({ features: toLines(v) })}
              />
              <TextControl
                label={__('Link text', 'foundations')}
                value={item.ctaLabel || ''}
                onChange={(ctaLabel) => update({ ctaLabel })}
              />
              <TextControl
                label={__('Link URL', 'foundations')}
                value={item.ctaUrl || ''}
                onChange={(ctaUrl) => update({ ctaUrl })}
              />
              <ToggleControl
                label={__('Mark as most chosen', 'foundations')}
                help={__('Darkens and lifts this card. Keep the tier wording saying so too.', 'foundations')}
                checked={!!item.featured}
                onChange={(featured) => update({ featured })}
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
