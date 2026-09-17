/**
 * Editor UI for foundations/halo-story. ServerSideRender previews render.php itself,
 * so the editor cannot drift from the front end.
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

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, body, facts, imageId, imageAlt, imageNote } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Introduction', 'foundations')}>
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
            label={__('Body', 'foundations')}
            help={__('Leave a blank line between paragraphs.', 'foundations')}
            value={body}
            rows={10}
            onChange={(v) => setAttributes({ body: v })}
          />
        </PanelBody>

        <PanelBody title={__('Portrait', 'foundations')} initialOpen={false}>
          <ToggleControl
            label={__('Show design placeholder', 'foundations')}
            checked={!!attributes.placeholder}
            onChange={(placeholder) => setAttributes({ placeholder })}
            help={__('Turn off after choosing the photograph.', 'foundations')}
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

          {imageId > 0 && imageAlt.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('This image has no alt text.', 'foundations')}
            </Notice>
          )}

          <TextareaControl
            label={__('Briefing note', 'foundations')}
            help={__('Shown only while the slot is empty. It is not alt text.', 'foundations')}
            value={imageNote}
            rows={2}
            onChange={(v) => setAttributes({ imageNote: v })}
          />
        </PanelBody>

        <Repeater
          items={facts}
          onChange={(next) => setAttributes({ facts: next })}
          blank={{ k: '', v: '' }}
          label={(item, i) => item.k || __('Fact', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add fact', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Label', 'foundations')}
                value={item.k || ''}
                onChange={(k) => update({ k })}
              />
              <TextControl
                label={__('Value', 'foundations')}
                value={item.v || ''}
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
