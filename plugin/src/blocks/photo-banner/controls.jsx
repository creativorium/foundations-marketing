/**
 * Sidebar controls for foundations/photo-banner.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Controls({ attributes, setAttributes }) {
  const { mediaId, mediaAlt, caption, spacedTop } = attributes;

  return (
    <PanelBody title={__('Photo', 'foundations')}>
      <MediaUploadCheck>
        <MediaUpload
          allowedTypes={['image']}
          value={mediaId}
          onSelect={(media) =>
            setAttributes({ mediaId: media.id, mediaAlt: media.alt || mediaAlt })
          }
          render={({ open }) => (
            <Button variant="secondary" onClick={open}>
              {mediaId ? __('Replace photo', 'foundations') : __('Choose photo', 'foundations')}
            </Button>
          )}
        />
      </MediaUploadCheck>

      {mediaId > 0 && (
        <Button
          variant="link"
          isDestructive
          onClick={() => setAttributes({ mediaId: 0 })}
          style={{ marginTop: 8 }}
        >
          {__('Remove photo', 'foundations')}
        </Button>
      )}

      <TextControl
        label={__('Alt text', 'foundations')}
        value={mediaAlt}
        onChange={(v) => setAttributes({ mediaAlt: v })}
      />

      {mediaId > 0 && mediaAlt.trim() === '' && (
        <Notice status="warning" isDismissible={false}>
          {__('This image has no alt text.', 'foundations')}
        </Notice>
      )}

      <TextareaControl
        label={__('Briefing note', 'foundations')}
        help={__(
          'Describes the photo to source. Shown only while the slot is empty — it is not alt text.',
          'foundations'
        )}
        value={caption}
        rows={3}
        onChange={(v) => setAttributes({ caption: v })}
      />

      <ToggleControl
        label={__('Space above', 'foundations')}
        checked={spacedTop}
        onChange={(v) => setAttributes({ spacedTop: v })}
      />
    </PanelBody>
  );
}
