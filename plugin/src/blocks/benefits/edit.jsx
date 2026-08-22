/**
 * Editor UI for foundations/benefits.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Edit({ attributes, setAttributes }) {
  const { number, label, aside, heading, items } = attributes;

  const blockProps = useBlockProps({ className: 'fm-benefits' });

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
          <TextControl
            label={__('Heading', 'foundations')}
            value={heading}
            onChange={(v) => setAttributes({ heading: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ n: '', title: '', body: '' }}
          label={(item, i) => item.title || __('Benefit', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add benefit', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Number', 'foundations')}
                value={item.n || ''}
                onChange={(v) => update({ n: v })}
              />
              <TextControl
                label={__('Title', 'foundations')}
                value={item.title || ''}
                onChange={(v) => update({ title: v })}
              />
              <TextareaControl
                label={__('Body', 'foundations')}
                value={item.body || ''}
                rows={4}
                onChange={(v) => update({ body: v })}
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

        {heading && <h2 className="fm-benefits__heading">{heading}</h2>}

        <ol className="fm-benefits__list">
          {items.length === 0 && (
            <li className="fm-benefits__item">
              {__('Add a benefit in the sidebar.', 'foundations')}
            </li>
          )}
          {items.map((item, i) => (
            <li key={i} className="fm-benefits__item">
              {item.n && <span className="fm-benefits__n">{item.n}</span>}
              <div className="fm-benefits__body">
                {item.title && <h3 className="fm-benefits__title">{item.title}</h3>}
                {item.body && <p>{item.body}</p>}
              </div>
            </li>
          ))}
        </ol>
      </section>
    </>
  );
}
