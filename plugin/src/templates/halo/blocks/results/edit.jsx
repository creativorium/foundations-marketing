/**
 * Editor UI for foundations/halo-results. ServerSideRender previews render.php itself.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

/** One half of a before/after pair. Both halves are the same three controls. */
function Shot({ item, update, side, title }) {
  const id = item[`${side}Id`] || 0;

  return (
    <>
      <p style={{ margin: '12px 0 4px', fontWeight: 600 }}>{title}</p>

      <MediaUploadCheck>
        <MediaUpload
          allowedTypes={['image']}
          value={id}
          onSelect={(media) =>
            update({ [`${side}Id`]: media.id, [`${side}Alt`]: media.alt || item[`${side}Alt`] || '' })
          }
          render={({ open }) => (
            <Button variant="secondary" onClick={open}>
              {id ? __('Replace photo', 'foundations') : __('Choose photo', 'foundations')}
            </Button>
          )}
        />
      </MediaUploadCheck>

      {id > 0 && (
        <Button
          variant="link"
          isDestructive
          style={{ marginLeft: 8 }}
          onClick={() => update({ [`${side}Id`]: 0 })}
        >
          {__('Remove', 'foundations')}
        </Button>
      )}

      <TextControl
        label={__('Alt text', 'foundations')}
        value={item[`${side}Alt`] || ''}
        onChange={(v) => update({ [`${side}Alt`]: v })}
      />
      <TextControl
        label={__('Briefing note', 'foundations')}
        help={__('Shown only while the slot is empty.', 'foundations')}
        value={item[`${side}Note`] || ''}
        onChange={(v) => update({ [`${side}Note`]: v })}
      />
    </>
  );
}

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, consentNote, items } = attributes;
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
            label={__('Consent note', 'foundations')}
            help={__(
              'Printed under the row. Every client photograph needs written consent before it goes live.',
              'foundations'
            )}
            value={consentNote}
            rows={3}
            onChange={(v) => setAttributes({ consentNote: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{
            label: '',
            caption: '',
            beforeId: 0,
            beforeAlt: '',
            beforeNote: 'client photo · before',
            afterId: 0,
            afterAlt: '',
            afterNote: 'client photo · after',
          }}
          label={(item, i) => item.label || __('Result', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add result', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Concern and timeline', 'foundations')}
                help={__('For example "Acne · 12 weeks".', 'foundations')}
                value={item.label || ''}
                onChange={(label) => update({ label })}
              />
              <TextControl
                label={__('What was done', 'foundations')}
                value={item.caption || ''}
                onChange={(caption) => update({ caption })}
              />
              <Shot item={item} update={update} side="before" title={__('Before', 'foundations')} />
              <Shot item={item} update={update} side="after" title={__('After', 'foundations')} />
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
