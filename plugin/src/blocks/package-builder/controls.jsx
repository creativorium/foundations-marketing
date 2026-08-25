/**
 * Sidebar controls for foundations/package-builder.
 *
 * Every price here is the price actually charged: plugin/inc/checkout.php reads these
 * same attributes back off the page when the order is priced, so editing a figure in this
 * sidebar changes the till. Nothing is duplicated in code.
 */
import {
  Notice,
  PanelBody,
  TextControl,
  TextareaControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

const lines = (value = []) => value.join('\n');
const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

// Ids are the handle the order is stored against, so they have to survive a rename of
// the visible label — hence a separate field rather than one derived from the name.
const slug = (value) =>
  value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');

export default function Controls({ attributes, setAttributes }) {
  const {
    basePrice,
    baseLabel,
    extraPagePrice,
    productId,
    previewHeading,
    extrasHeading,
    reviewHeading,
    included,
    freeItems,
    addons,
    extraPages,
    afterSteps,
  } = attributes;

  const number = (value, fallback) => {
    const n = parseInt(value, 10);
    return Number.isNaN(n) ? fallback : Math.max(0, n);
  };

  return (
    <>
      <PanelBody title={__('Pricing', 'foundations')}>
        {!productId && (
          <Notice status="warning" isDismissible={false}>
            {__(
              'No WooCommerce product linked. The Pay button cannot take payment until you set one.',
              'foundations'
            )}
          </Notice>
        )}
        <TextControl
          label={__('WooCommerce product ID', 'foundations')}
          help={__('The product the order is placed against.', 'foundations')}
          type="number"
          value={String(productId ?? 0)}
          onChange={(v) => setAttributes({ productId: number(v, 0) })}
        />
        <TextControl
          label={__('Base price', 'foundations')}
          type="number"
          value={String(basePrice ?? 0)}
          onChange={(v) => setAttributes({ basePrice: number(v, 249) })}
        />
        <TextControl
          label={__('What the base price covers', 'foundations')}
          help={__('Shown on the order line after the template name.', 'foundations')}
          value={baseLabel || ''}
          onChange={(v) => setAttributes({ baseLabel: v })}
        />
        <TextControl
          label={__('Price per extra page', 'foundations')}
          type="number"
          value={String(extraPagePrice ?? 0)}
          onChange={(v) => setAttributes({ extraPagePrice: number(v, 50) })}
        />
      </PanelBody>

      <PanelBody title={__('Headings', 'foundations')} initialOpen={false}>
        <TextControl
          label={__('Step 1 eyebrow', 'foundations')}
          value={previewHeading || ''}
          onChange={(v) => setAttributes({ previewHeading: v })}
        />
        <TextControl
          label={__('Step 2 heading', 'foundations')}
          value={extrasHeading || ''}
          onChange={(v) => setAttributes({ extrasHeading: v })}
        />
        <TextControl
          label={__('Step 3 heading', 'foundations')}
          value={reviewHeading || ''}
          onChange={(v) => setAttributes({ reviewHeading: v })}
        />
      </PanelBody>

      <PanelBody title={__('What is included', 'foundations')} initialOpen={false}>
        <TextareaControl
          label={__('Included as standard', 'foundations')}
          help={__('One per line.', 'foundations')}
          rows={7}
          value={lines(included)}
          onChange={(v) => setAttributes({ included: toLines(v) })}
        />
        <TextareaControl
          label={__('Also free', 'foundations')}
          help={__('One per line. These also appear on the order summary at zero.', 'foundations')}
          rows={3}
          value={lines(freeItems)}
          onChange={(v) => setAttributes({ freeItems: toLines(v) })}
        />
      </PanelBody>

      <PanelBody title={__('Add-ons', 'foundations')} initialOpen={false}>
        <Repeater
          items={addons}
          onChange={(next) => setAttributes({ addons: next })}
          blank={{ id: '', name: '', price: 0, body: '', when: '' }}
          label={(item, i) => item.name || __('Add-on', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add an add-on', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Name', 'foundations')}
                value={item.name || ''}
                onChange={(v) =>
                  update(item.id ? { name: v } : { name: v, id: slug(v) })
                }
              />
              <TextControl
                label={__('ID', 'foundations')}
                help={__('Stored on the order. Changing it orphans past orders.', 'foundations')}
                value={item.id || ''}
                onChange={(v) => update({ id: slug(v) })}
              />
              <TextControl
                label={__('Price', 'foundations')}
                type="number"
                value={String(item.price ?? 0)}
                onChange={(v) => update({ price: number(v, 0) })}
              />
              <TextareaControl
                label={__('Description', 'foundations')}
                rows={3}
                value={item.body || ''}
                onChange={(v) => update({ body: v })}
              />
              <TextControl
                label={__('When it is worth it', 'foundations')}
                value={item.when || ''}
                onChange={(v) => update({ when: v })}
              />
            </>
          )}
        </Repeater>
      </PanelBody>

      <PanelBody title={__('Extra pages', 'foundations')} initialOpen={false}>
        <Repeater
          items={extraPages}
          onChange={(next) => setAttributes({ extraPages: next })}
          blank={{ id: '', name: '', body: '' }}
          label={(item, i) => item.name || __('Page', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add a page', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Name', 'foundations')}
                value={item.name || ''}
                onChange={(v) =>
                  update(item.id ? { name: v } : { name: v, id: slug(v) })
                }
              />
              <TextControl
                label={__('ID', 'foundations')}
                value={item.id || ''}
                onChange={(v) => update({ id: slug(v) })}
              />
              <TextareaControl
                label={__('Description', 'foundations')}
                rows={2}
                value={item.body || ''}
                onChange={(v) => update({ body: v })}
              />
            </>
          )}
        </Repeater>
      </PanelBody>

      <PanelBody title={__('What happens after you pay', 'foundations')} initialOpen={false}>
        <Repeater
          items={afterSteps}
          onChange={(next) => setAttributes({ afterSteps: next })}
          blank={{ when: '', what: '' }}
          label={(item, i) => item.when || __('Stage', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add a stage', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('When', 'foundations')}
                value={item.when || ''}
                onChange={(v) => update({ when: v })}
              />
              <TextareaControl
                label={__('What', 'foundations')}
                rows={3}
                value={item.what || ''}
                onChange={(v) => update({ what: v })}
              />
            </>
          )}
        </Repeater>
      </PanelBody>
    </>
  );
}
