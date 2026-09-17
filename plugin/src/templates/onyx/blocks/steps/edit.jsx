/**
 * Editor UI for foundations/onyx-steps.
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
        <PanelBody title={__('Section intro', 'foundations')}>
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
          blank={{ num: '', name: '', desc: '' }}
          label={(item, i) => item.name || __('Step', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add step', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Number', 'foundations')}
                value={item.num || ''}
                onChange={(v) => update({ num: v })}
              />
              <TextControl
                label={__('Name', 'foundations')}
                value={item.name || ''}
                onChange={(v) => update({ name: v })}
              />
              <TextareaControl
                label={__('Description', 'foundations')}
                value={item.desc || ''}
                rows={3}
                onChange={(v) => update({ desc: v })}
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
