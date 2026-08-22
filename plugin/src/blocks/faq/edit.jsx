/**
 * Editor UI for foundations/faq.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Edit({ attributes, setAttributes }) {
  const { number, label, aside, heading, headingAccent, items, emitSchema } = attributes;

  const blockProps = useBlockProps({ className: 'fm-faq' });

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
          <TextControl
            label={__('Heading accent line', 'foundations')}
            value={headingAccent}
            onChange={(v) => setAttributes({ headingAccent: v })}
          />
        </PanelBody>

        <PanelBody title={__('Search results', 'foundations')}>
          <ToggleControl
            label={__('Output FAQ schema', 'foundations')}
            help={__(
              'Lets Google show these questions directly in search results. Only one FAQ block per page should have this on.',
              'foundations'
            )}
            checked={emitSchema}
            onChange={(v) => setAttributes({ emitSchema: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ q: '', a: '' }}
          label={(item, i) => item.q || __('Question', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add question', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextareaControl
                label={__('Question', 'foundations')}
                value={item.q || ''}
                rows={2}
                onChange={(v) => update({ q: v })}
              />
              <TextareaControl
                label={__('Answer', 'foundations')}
                value={item.a || ''}
                rows={5}
                onChange={(v) => update({ a: v })}
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

        <div className="fm-faq__grid">
          {(heading || headingAccent) && (
            <h2 className="fm-faq__heading">
              {heading}
              {headingAccent && (
                <span className="fm-faq__heading-accent">{headingAccent}</span>
              )}
            </h2>
          )}

          <div className="fm-faq__list">
            {items.length === 0 && <p>{__('Add a question in the sidebar.', 'foundations')}</p>}
            {items.map((item, i) => (
              <div key={i} className="fm-faq__item">
                <p className="fm-faq__q">
                  <span>{item.q}</span>
                </p>
                <p className="fm-faq__a">{item.a}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
