/**
 * Editor UI for foundations/halo-contact. ServerSideRender previews render.php itself.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  Button,
  Notice,
  PanelBody,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const {
    eyebrow,
    heading,
    lead,
    action,
    interestLabel,
    interests,
    messageLabel,
    submitLabel,
    imageId,
    imageAlt,
    imageNote,
  } = attributes;

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

        <PanelBody title={__('The form', 'foundations')}>
          <TextControl
            label={__('Where submissions go', 'foundations')}
            help={__(
              'The form posts here. Until it is set, the button is disabled — nothing in this template can deliver an enquiry on its own.',
              'foundations'
            )}
            value={action}
            onChange={(v) => setAttributes({ action: v })}
          />
          {action.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('No destination set, so the form cannot send anything yet.', 'foundations')}
            </Notice>
          )}
          <TextControl
            label={__('Interest label', 'foundations')}
            value={interestLabel}
            onChange={(v) => setAttributes({ interestLabel: v })}
          />
          <TextareaControl
            label={__('Interest options', 'foundations')}
            help={__('One per line. Leave empty to drop the field.', 'foundations')}
            value={interests.join('\n')}
            rows={6}
            onChange={(v) => setAttributes({ interests: toLines(v) })}
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

        <PanelBody title={__('Studio photograph', 'foundations')} initialOpen={false}>
          <ToggleControl
            label={__('Show design placeholder', 'foundations')}
            checked={!!attributes.placeholder}
            onChange={(placeholder) => setAttributes({ placeholder })}
          />

          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              value={imageId}
              onSelect={(media) =>
                setAttributes({ imageId: media.id, imageAlt: media.alt || imageAlt })
              }
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {imageId ? __('Replace image', 'foundations') : __('Choose image', 'foundations')}
                </Button>
              )}
            />
          </MediaUploadCheck>

          {imageId > 0 && (
            <Button
              variant="link"
              isDestructive
              style={{ marginTop: 8 }}
              onClick={() => setAttributes({ imageId: 0 })}
            >
              {__('Remove image', 'foundations')}
            </Button>
          )}

          <TextControl
            label={__('Alt text', 'foundations')}
            value={imageAlt}
            onChange={(v) => setAttributes({ imageAlt: v })}
          />
          <TextControl
            label={__('Briefing note', 'foundations')}
            help={__('Shown only while the slot is empty.', 'foundations')}
            value={imageNote}
            onChange={(v) => setAttributes({ imageNote: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
