/**
 * Editor UI for foundations/halo-social. ServerSideRender previews render.php itself.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { handle, linkLabel, linkUrl, posts } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Account', 'foundations')}>
          <TextControl
            label={__('Handle', 'foundations')}
            value={handle}
            onChange={(v) => setAttributes({ handle: v })}
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

        <Repeater
          items={posts}
          onChange={(next) => setAttributes({ posts: next })}
          blank={{ id: 0, alt: '', note: '' }}
          label={(item, i) => item.alt || __('Post', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add post', 'foundations')}
        >
          {(item, update) => (
            <>
              <MediaUploadCheck>
                <MediaUpload
                  allowedTypes={['image']}
                  value={item.id || 0}
                  onSelect={(media) => update({ id: media.id, alt: media.alt || item.alt || '' })}
                  render={({ open }) => (
                    <Button variant="secondary" onClick={open}>
                      {item.id ? __('Replace image', 'foundations') : __('Choose image', 'foundations')}
                    </Button>
                  )}
                />
              </MediaUploadCheck>

              {item.id > 0 && (
                <Button
                  variant="link"
                  isDestructive
                  style={{ marginLeft: 8 }}
                  onClick={() => update({ id: 0 })}
                >
                  {__('Remove', 'foundations')}
                </Button>
              )}

              <TextControl
                label={__('Alt text', 'foundations')}
                value={item.alt || ''}
                onChange={(alt) => update({ alt })}
              />
              <TextControl
                label={__('Briefing note', 'foundations')}
                help={__('Shown only while the slot is empty.', 'foundations')}
                value={item.note || ''}
                onChange={(note) => update({ note })}
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
