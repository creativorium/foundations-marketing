/**
 * Editor UI for foundations/halo-concerns. ServerSideRender previews render.php itself,
 * so the editor cannot drift from the front end.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, lead, items } = attributes;
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
          <TextareaControl
            label={__('Heading', 'foundations')}
            value={heading}
            rows={2}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Standfirst', 'foundations')}
            value={lead}
            rows={3}
            onChange={(v) => setAttributes({ lead: v })}
          />
        </PanelBody>

        <Repeater
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ label: '', url: '' }}
          label={(item, i) => item.label || __('Concern', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add concern', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Concern', 'foundations')}
                value={item.label || ''}
                onChange={(label) => update({ label })}
              />
              <TextControl
                label={__('Link URL', 'foundations')}
                help={__('Leave empty to show it as plain text.', 'foundations')}
                value={item.url || ''}
                onChange={(url) => update({ url })}
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
