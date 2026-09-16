import { ToggleControl } from '@wordpress/components';
/**
 * Editor UI for foundations/onyx-feature.
 *
 * `body` and `credentials` are arrays of plain strings, not records, so they are edited
 * as one-per-line textareas rather than through the Repeater. A repeater panel per
 * paragraph would be three clicks to change a sentence.
 */
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/** One-per-line text to an array, dropping blank lines so a stray return adds nothing. */
const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const {
    eyebrow,
    heading,
    body,
    credentials,
    linkLabel,
    linkUrl,
    imageId,
    imageAlt,
    imageNote,
  } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls><PanelBody title="Image placeholder"><ToggleControl label="Show design placeholder" checked={!!attributes.placeholder} onChange={placeholder => setAttributes({placeholder})} help="Turn off after choosing the customer's photograph." /></PanelBody>
        <PanelBody title={__('Copy', 'foundations')}>
          <TextControl
            label={__('Eyebrow', 'foundations')}
            value={eyebrow}
            onChange={(v) => setAttributes({ eyebrow: v })}
          />
          <TextareaControl
            label={__('Heading', 'foundations')}
            value={heading}
            rows={3}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Paragraphs', 'foundations')}
            help={__('One paragraph per line.', 'foundations')}
            value={body.join('\n')}
            rows={6}
            onChange={(v) => setAttributes({ body: toLines(v) })}
          />
          <TextareaControl
            label={__('Credentials', 'foundations')}
            help={__('One per line. Rendered as pills.', 'foundations')}
            value={credentials.join('\n')}
            rows={4}
            onChange={(v) => setAttributes({ credentials: toLines(v) })}
          />
          <TextControl
            label={__('Link text', 'foundations')}
            value={linkLabel}
            onChange={(v) => setAttributes({ linkLabel: v })}
          />
          <TextControl
            label={__('Link URL', 'foundations')}
            value={linkUrl}
            onChange={(v) => setAttributes({ linkUrl: v })}
          />
        </PanelBody>

        <PanelBody title={__('Portrait', 'foundations')} initialOpen={false}>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              value={imageId}
              onSelect={(media) =>
                setAttributes({ imageId: media.id, imageAlt: media.alt || imageAlt })
              }
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {imageId
                    ? __('Replace portrait', 'foundations')
                    : __('Choose portrait', 'foundations')}
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
              {__('Remove portrait', 'foundations')}
            </Button>
          )}

          <TextControl
            label={__('Alt text', 'foundations')}
            value={imageAlt}
            onChange={(v) => setAttributes({ imageAlt: v })}
          />

          {imageId > 0 && imageAlt.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('This image has no alt text.', 'foundations')}
            </Notice>
          )}

          <TextareaControl
            label={__('Briefing note', 'foundations')}
            help={__('Shown only while the slot is empty. Not alt text.', 'foundations')}
            value={imageNote}
            rows={2}
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
