/**
 * Editor UI for foundations/photo-strip.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import { Button, Notice, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Edit({ attributes, setAttributes }) {
  const { items } = attributes;

  const blockProps = useBlockProps({ className: 'fm-photo-strip' });

  const missingAlt = items.some((item) => item.id > 0 && !(item.alt || '').trim());

  return (
    <>
      <InspectorControls>
        {missingAlt && (
          <div style={{ padding: 16 }}>
            <Notice status="warning" isDismissible={false}>
              {__('One or more photos have no alt text.', 'foundations')}
            </Notice>
          </div>
        )}

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ id: 0, alt: '', caption: '' }}
          label={(item, i) => item.alt || __('Photo', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add photo', 'foundations')}
        >
          {(item, update) => (
            <>
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  value={item.id}
                  onSelect={(media) =>
                    update({ id: media.id, alt: media.alt || item.alt || '' })
                  }
                  render={({ open }) => (
                    <Button variant="secondary" onClick={open}>
                      {item.id
                        ? __('Replace photo', 'foundations')
                        : __('Choose photo', 'foundations')}
                    </Button>
                  )}
                />
              </MediaUploadCheck>

              {item.id > 0 && (
                <Button
                  variant="link"
                  isDestructive
                  onClick={() => update({ id: 0 })}
                  style={{ marginTop: 8 }}
                >
                  {__('Remove photo', 'foundations')}
                </Button>
              )}

              <TextControl
                label={__('Alt text', 'foundations')}
                help={__('Describe the photo. Required unless it is purely decorative.', 'foundations')}
                value={item.alt || ''}
                onChange={(v) => update({ alt: v })}
              />
              <TextControl
                label={__('Placeholder note', 'foundations')}
                help={__('Shown only while no photo is chosen — a reminder of what goes here.', 'foundations')}
                value={item.caption || ''}
                onChange={(v) => update({ caption: v })}
              />
            </>
          )}
        </Repeater>
      </InspectorControls>

      <div {...blockProps}>
        {items.length === 0 && (
          <figure className="fm-photo-strip__cell">
            <span className="fm-photo-strip__placeholder">
              <span className="fm-photo-strip__tag">
                {__('Add a photo in the sidebar', 'foundations')}
              </span>
            </span>
          </figure>
        )}
        {items.map((item, i) => (
          <figure key={i} className="fm-photo-strip__cell">
            <span className="fm-photo-strip__placeholder">
              <span className="fm-photo-strip__tag">
                {item.id ? __('Photo set', 'foundations') : '[ photo ]'}
              </span>
              {item.caption && <span className="fm-photo-strip__hint">{item.caption}</span>}
            </span>
          </figure>
        ))}
      </div>
    </>
  );
}
