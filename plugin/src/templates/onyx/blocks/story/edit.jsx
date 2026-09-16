import { ToggleControl } from '@wordpress/components';
/**
 * Editor UI for foundations/onyx-story.
 */
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
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
  const { standfirst, body, timeline, imageId, imageAlt, imageNote } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls><PanelBody title="Image placeholder"><ToggleControl label="Show design placeholder" checked={!!attributes.placeholder} onChange={placeholder => setAttributes({placeholder})} help="Turn off after choosing the customer's photograph." /></PanelBody>
        <PanelBody title={__('Copy', 'foundations')}>
          <TextareaControl
            label={__('Opening paragraph', 'foundations')}
            help={__('Set slightly larger than the rest.', 'foundations')}
            value={standfirst}
            rows={5}
            onChange={(v) => setAttributes({ standfirst: v })}
          />
          <TextareaControl
            label={__('Body paragraphs', 'foundations')}
            help={__('One paragraph per line.', 'foundations')}
            value={body.join('\n')}
            rows={8}
            onChange={(v) => setAttributes({ body: toLines(v) })}
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

        <Repeater
          items={timeline}
          onChange={(next) => setAttributes({ timeline: next })}
          blank={{ year: '', what: '' }}
          label={(item, i) => item.year || __('Entry', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add timeline entry', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Year', 'foundations')}
                value={item.year || ''}
                onChange={(v) => update({ year: v })}
              />
              <TextareaControl
                label={__('What happened', 'foundations')}
                value={item.what || ''}
                rows={3}
                onChange={(v) => update({ what: v })}
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
