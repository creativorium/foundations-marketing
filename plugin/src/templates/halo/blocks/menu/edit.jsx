/**
 * Editor UI for foundations/halo-menu. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, note, items } = attributes;
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
            label={__('Note', 'foundations')}
            help={__('The line beside the heading.', 'foundations')}
            value={note}
            rows={3}
            onChange={(v) => setAttributes({ note: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ name: '', body: '', duration: '', price: '' }}
          label={(item, i) => item.name || __('Treatment', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add treatment', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Treatment', 'foundations')}
                value={item.name || ''}
                onChange={(name) => update({ name })}
              />
              <TextareaControl
                label={__('Description', 'foundations')}
                value={item.body || ''}
                rows={3}
                onChange={(body) => update({ body })}
              />
              <TextControl
                label={__('Length', 'foundations')}
                help={__('For example "60 min".', 'foundations')}
                value={item.duration || ''}
                onChange={(duration) => update({ duration })}
              />
              <TextControl
                label={__('Price', 'foundations')}
                help={__('Written as it should read — "£85", or "Free".', 'foundations')}
                value={item.price || ''}
                onChange={(price) => update({ price })}
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
