/**
 * Editor UI for foundations/quotes.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  Button,
  PanelBody,
  TextControl,
  TextareaControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Edit({ attributes, setAttributes }) {
  const {
    number,
    label,
    aside,
    founderQuote,
    founderName,
    founderRole,
    founderMediaId,
    founderMediaAlt,
    items,
    openSlot,
  } = attributes;

  const blockProps = useBlockProps({ className: 'fm-quotes' });

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

        <PanelBody title={__('Founder quote', 'foundations')}>
          <TextareaControl
            label={__('Quote', 'foundations')}
            value={founderQuote}
            rows={6}
            onChange={(v) => setAttributes({ founderQuote: v })}
          />
          <TextControl
            label={__('Name', 'foundations')}
            value={founderName}
            onChange={(v) => setAttributes({ founderName: v })}
          />
          <TextControl
            label={__('Role', 'foundations')}
            value={founderRole}
            onChange={(v) => setAttributes({ founderRole: v })}
          />

          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              value={founderMediaId}
              onSelect={(media) =>
                setAttributes({
                  founderMediaId: media.id,
                  founderMediaAlt: media.alt || founderMediaAlt,
                })
              }
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {founderMediaId
                    ? __('Replace portrait', 'foundations')
                    : __('Choose portrait', 'foundations')}
                </Button>
              )}
            />
          </MediaUploadCheck>

          {founderMediaId > 0 && (
            <Button
              variant="link"
              isDestructive
              onClick={() => setAttributes({ founderMediaId: 0 })}
              style={{ marginTop: 8 }}
            >
              {__('Remove portrait', 'foundations')}
            </Button>
          )}

          <TextControl
            label={__('Portrait alt text', 'foundations')}
            value={founderMediaAlt}
            onChange={(v) => setAttributes({ founderMediaAlt: v })}
          />
        </PanelBody>

        <PanelBody title={__('Open slot', 'foundations')} initialOpen={false}>
          <TextareaControl
            label={__('Placeholder card text', 'foundations')}
            help={__(
              'The deliberately empty card inviting a first testimonial. Leave blank to hide it.',
              'foundations'
            )}
            value={openSlot}
            rows={4}
            onChange={(v) => setAttributes({ openSlot: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ text: '', name: '', role: '' }}
          label={(item, i) => item.name || __('Quote', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add testimonial', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextareaControl
                label={__('Quote', 'foundations')}
                value={item.text || ''}
                rows={5}
                onChange={(v) => update({ text: v })}
              />
              <TextControl
                label={__('Name', 'foundations')}
                value={item.name || ''}
                onChange={(v) => update({ name: v })}
              />
              <TextControl
                label={__('Role', 'foundations')}
                value={item.role || ''}
                onChange={(v) => update({ role: v })}
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

        <div className="fm-quotes__grid">
          {founderQuote && (
            <figure className="fm-quotes__founder">
              <div className="fm-quotes__portrait">
                <span className="fm-quotes__placeholder">
                  <span className="fm-quotes__tag">
                    {founderMediaId ? __('Portrait set', 'foundations') : '[ photo ]'}
                  </span>
                </span>
              </div>
              <div className="fm-quotes__founder-body">
                <blockquote className="fm-quotes__founder-text">
                  <p>{founderQuote}</p>
                </blockquote>
                <figcaption className="fm-quotes__attribution">
                  <span className="fm-quotes__name">{founderName}</span>
                  <span className="fm-quotes__role">{founderRole}</span>
                </figcaption>
              </div>
            </figure>
          )}

          <div className="fm-quotes__list">
            {items.map((item, i) => (
              <figure key={i} className="fm-quotes__item">
                <blockquote>
                  <p>{item.text}</p>
                </blockquote>
                <figcaption className="fm-quotes__attribution">
                  <span className="fm-quotes__name">{item.name}</span>
                  <span className="fm-quotes__role">{item.role}</span>
                </figcaption>
              </figure>
            ))}
            {openSlot && <p className="fm-quotes__open">{openSlot}</p>}
          </div>
        </div>
      </section>
    </>
  );
}
