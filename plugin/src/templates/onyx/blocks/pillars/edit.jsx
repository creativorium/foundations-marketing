/**
 * Editor UI for foundations/onyx-pillars.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, lead, linkLabel, linkUrl, items } = attributes;

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
          <TextareaControl
            label={__('Lead', 'foundations')}
            value={lead}
            rows={4}
            onChange={(v) => setAttributes({ lead: v })}
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
          items={items}
          onChange={(next) => setAttributes({ items: next })}
          blank={{ num: '', name: '', desc: '', from: '', url: '' }}
          label={(item, i) => item.name || __('Pillar', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add pillar', 'foundations')}
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
              <TextControl
                label={__('From price', 'foundations')}
                value={item.from || ''}
                onChange={(v) => update({ from: v })}
              />
              <TextControl
                label={__('Links to', 'foundations')}
                help={__('Leave empty and the card renders as plain text, not a link.', 'foundations')}
                value={item.url || ''}
                onChange={(v) => update({ url: v })}
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
