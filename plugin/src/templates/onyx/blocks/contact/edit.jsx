/**
 * Editor UI for foundations/onyx-contact.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Notice, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const {
    action,
    areaLabel,
    areas,
    messageLabel,
    consentText,
    consentUrl,
    consentLink,
    submitLabel,
    cards,
    mapNote,
  } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Where enquiries go', 'foundations')}>
          <TextControl
            label={__('Form endpoint', 'foundations')}
            help={__(
              'The URL that receives the submission. Nothing in this template processes one — a form handler is separate work. Until this is set the submit button is disabled and says why.',
              'foundations'
            )}
            value={action}
            onChange={(v) => setAttributes({ action: v })}
          />

          {action.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('No endpoint set — the form cannot send anything yet.', 'foundations')}
            </Notice>
          )}
        </PanelBody>

        <PanelBody title={__('Fields', 'foundations')} initialOpen={false}>
          <TextControl
            label={__('Area-of-interest label', 'foundations')}
            value={areaLabel}
            onChange={(v) => setAttributes({ areaLabel: v })}
          />
          <TextareaControl
            label={__('Area options', 'foundations')}
            help={__('One per line. Leave empty to drop the field.', 'foundations')}
            value={areas.join('\n')}
            rows={5}
            onChange={(v) => setAttributes({ areas: toLines(v) })}
          />
          <TextControl
            label={__('Message label', 'foundations')}
            value={messageLabel}
            onChange={(v) => setAttributes({ messageLabel: v })}
          />
          <TextControl
            label={__('Submit button text', 'foundations')}
            value={submitLabel}
            onChange={(v) => setAttributes({ submitLabel: v })}
          />
        </PanelBody>

        <PanelBody title={__('Consent', 'foundations')} initialOpen={false}>
          <TextareaControl
            label={__('Consent text', 'foundations')}
            help={__('Leave empty to drop the checkbox.', 'foundations')}
            value={consentText}
            rows={4}
            onChange={(v) => setAttributes({ consentText: v })}
          />
          <TextControl
            label={__('Link text', 'foundations')}
            value={consentLink}
            onChange={(v) => setAttributes({ consentLink: v })}
          />
          <TextControl
            label={__('Link URL', 'foundations')}
            value={consentUrl}
            onChange={(v) => setAttributes({ consentUrl: v })}
          />
        </PanelBody>

        <PanelBody title={__('Map slot', 'foundations')} initialOpen={false}>
          <TextareaControl
            label={__('Placeholder note', 'foundations')}
            help={__('Leave empty to drop the map slot entirely.', 'foundations')}
            value={mapNote}
            rows={2}
            onChange={(v) => setAttributes({ mapNote: v })}
          />
        </PanelBody>

        <Repeater
          items={cards}
          onChange={(next) => setAttributes({ cards: next })}
          blank={{ k: '', v: '' }}
          label={(item, i) => item.k || __('Card', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add contact card', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Label', 'foundations')}
                value={item.k || ''}
                onChange={(v) => update({ k: v })}
              />
              <TextareaControl
                label={__('Value', 'foundations')}
                help={__('Line breaks are kept.', 'foundations')}
                value={item.v || ''}
                rows={3}
                onChange={(v) => update({ v })}
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
