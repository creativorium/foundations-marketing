/**
 * Editor UI for foundations/halo-faq. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, items } = attributes;
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
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ q: '', a: '' }}
          label={(item, i) => item.q || __('Question', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add question', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextareaControl
                label={__('Question', 'foundations')}
                value={item.q || ''}
                rows={2}
                onChange={(q) => update({ q })}
              />
              <TextareaControl
                label={__('Answer', 'foundations')}
                value={item.a || ''}
                rows={5}
                onChange={(a) => update({ a })}
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
