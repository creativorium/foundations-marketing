/**
 * Sidebar controls for foundations/audience.
 */
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Controls({ attributes, setAttributes }) {
  const { number, label, aside, heading, headingAccent, tags, mediaId, mediaAlt, caption } =
    attributes;

  return (
    <>
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
          label={__('Accent line', 'foundations')}
          value={headingAccent}
          onChange={(v) => setAttributes({ headingAccent: v })}
        />
      </PanelBody>

      <PanelBody title={__('Tags', 'foundations')}>
        <TextareaControl
          label={__('One per line', 'foundations')}
          help={__('The professions served. Each renders as a pill.', 'foundations')}
          value={tags}
          rows={12}
          onChange={(v) => setAttributes({ tags: v })}
        />
      </PanelBody>

      <PanelBody title={__('Portrait', 'foundations')}>
        <MediaUploadCheck>
          <MediaUpload
            allowedTypes={['image']}
            value={mediaId}
            onSelect={(media) =>
              setAttributes({ mediaId: media.id, mediaAlt: media.alt || mediaAlt })
            }
            render={({ open }) => (
              <Button variant="secondary" onClick={open}>
                {mediaId ? __('Replace portrait', 'foundations') : __('Choose portrait', 'foundations')}
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
            {__('Remove portrait', 'foundations')}
          </Button>
        )}

        <TextControl
          label={__('Alt text', 'foundations')}
          value={mediaAlt}
          onChange={(v) => setAttributes({ mediaAlt: v })}
        />
        <TextareaControl
          label={__('Briefing note', 'foundations')}
          value={caption}
          rows={3}
          onChange={(v) => setAttributes({ caption: v })}
        />
      </PanelBody>
    </>
  );
}
