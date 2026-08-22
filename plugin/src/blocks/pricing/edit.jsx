/**
 * Editor UI for foundations/pricing.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Edit({ attributes, setAttributes }) {
  const {
    number,
    label,
    aside,
    prefix,
    price,
    lede,
    ctaText,
    ctaUrl,
    smallprint,
    includedTitle,
    included,
    addonsTitle,
    addons,
    addonsNote,
  } = attributes;

  const blockProps = useBlockProps({ className: 'fm-pricing' });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Section rule', 'foundations')}>
          <TextControl
            label={__('Number', 'foundations')}
            value={number}
            onChange={(v) => setAttributes({ number: v })}
          />
          <TextControl
            label={__('Label', 'foundations')}
            value={label}
            onChange={(v) => setAttributes({ label: v })}
          />
          <TextControl
            label={__('Aside', 'foundations')}
            value={aside}
            onChange={(v) => setAttributes({ aside: v })}
          />
        </PanelBody>

        <PanelBody title={__('Headline', 'foundations')}>
          <TextControl
            label={__('Prefix', 'foundations')}
            value={prefix}
            onChange={(v) => setAttributes({ prefix: v })}
          />
          <TextControl
            label={__('Price', 'foundations')}
            value={price}
            onChange={(v) => setAttributes({ price: v })}
          />
          <TextareaControl
            label={__('Lede', 'foundations')}
            value={lede}
            rows={3}
            onChange={(v) => setAttributes({ lede: v })}
          />
          <TextControl
            label={__('Button label', 'foundations')}
            value={ctaText}
            onChange={(v) => setAttributes({ ctaText: v })}
          />
          <TextControl
            label={__('Button link', 'foundations')}
            value={ctaUrl}
            onChange={(v) => setAttributes({ ctaUrl: v })}
          />
          <TextareaControl
            label={__('Small print', 'foundations')}
            value={smallprint}
            rows={3}
            onChange={(v) => setAttributes({ smallprint: v })}
          />
        </PanelBody>

        <PanelBody title={__('Included list', 'foundations')} initialOpen={false}>
          <TextControl
            label={__('Card title', 'foundations')}
            value={includedTitle}
            onChange={(v) => setAttributes({ includedTitle: v })}
          />
          <TextareaControl
            label={__('Items, one per line', 'foundations')}
            value={included.map((i) => (typeof i === 'string' ? i : i.text || '')).join('\n')}
            rows={8}
            onChange={(v) =>
              setAttributes({
                included: v
                  .split('\n')
                  .map((s) => s.trim())
                  .filter(Boolean)
                  .map((text) => ({ text })),
              })
            }
          />
        </PanelBody>

        <PanelBody title={__('Add-ons', 'foundations')} initialOpen={false}>
          <TextControl
            label={__('Card title', 'foundations')}
            value={addonsTitle}
            onChange={(v) => setAttributes({ addonsTitle: v })}
          />
          <TextareaControl
            label={__('Note', 'foundations')}
            value={addonsNote}
            rows={3}
            onChange={(v) => setAttributes({ addonsNote: v })}
          />
        </PanelBody>

        <Repeater
          items={addons}
          onChange={(next) => setAttributes({ addons: next })}
          blank={{ name: '', price: '' }}
          label={(item, i) => item.name || __('Add-on', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add add-on', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Name', 'foundations')}
                value={item.name || ''}
                onChange={(v) => update({ name: v })}
              />
              <TextControl
                label={__('Price', 'foundations')}
                value={item.price || ''}
                onChange={(v) => update({ price: v })}
              />
            </>
          )}
        </Repeater>
      </InspectorControls>

      <section {...blockProps}>
        {(number || label) && (
          <div className="fm-section-rule">
            <span>{[number, label].filter(Boolean).join(' — ')}</span>
            {aside && <span className="fm-section-rule__aside">{aside}</span>}
          </div>
        )}

        <div className="fm-pricing__grid">
          <div className="fm-pricing__headline">
            <p className="fm-pricing__figure">
              {prefix && <span className="fm-pricing__prefix">{prefix}</span>}
              <span className="fm-pricing__amount">{price}</span>
            </p>
            {lede && <p className="fm-pricing__lede">{lede}</p>}
            {ctaText && <span className="fm-pricing__cta">{ctaText} &rarr;</span>}
            {smallprint && <p className="fm-pricing__smallprint">{smallprint}</p>}
          </div>

          <div className="fm-pricing__cards">
            {included.length > 0 && (
              <div className="fm-pricing__card fm-pricing__card--included">
                <h3 className="fm-pricing__card-title">{includedTitle}</h3>
                <ul className="fm-pricing__list">
                  {included.map((item, i) => (
                    <li key={i} className="fm-pricing__row">
                      <span className="fm-pricing__plus">+</span>
                      <span>{typeof item === 'string' ? item : item.text}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {addons.length > 0 && (
              <div className="fm-pricing__card fm-pricing__card--addons">
                <h3 className="fm-pricing__card-title">{addonsTitle}</h3>
                <ul className="fm-pricing__list">
                  {addons.map((item, i) => (
                    <li key={i} className="fm-pricing__row fm-pricing__row--split">
                      <span>{item.name}</span>
                      <span className="fm-pricing__addon-price">{item.price}</span>
                    </li>
                  ))}
                </ul>
                {addonsNote && <p className="fm-pricing__note">{addonsNote}</p>}
              </div>
            )}
          </div>
        </div>
      </section>
    </>
  );
}
