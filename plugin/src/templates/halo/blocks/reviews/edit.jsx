/**
 * Editor UI for foundations/halo-reviews. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, quotes } = attributes;
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
        </PanelBody>

        <Repeater
          items={quotes}
          onChange={(next) => setAttributes({ quotes: next })}
          blank={{ quote: '', by: '' }}
          label={(item, i) => item.by || __('Quote', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add quote', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextareaControl
                label={__('Quote', 'foundations')}
                value={item.quote || ''}
                rows={4}
                onChange={(quote) => update({ quote })}
              />
              <TextControl
                label={__('Who said it', 'foundations')}
                help={__('A first name and a context, as in the original.', 'foundations')}
                value={item.by || ''}
                onChange={(by) => update({ by })}
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
