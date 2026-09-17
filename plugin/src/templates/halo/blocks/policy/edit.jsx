/**
 * Editor UI for foundations/halo-policy. ServerSideRender previews render.php itself.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

// Paragraphs are separated by a blank line, bullets by a newline. Both edit as text
// rather than as repeaters inside a repeater, which no one can work in.
const toParagraphs = (value) =>
  value
    .split(/\n{2,}/)
    .map((part) => part.trim())
    .filter(Boolean);

const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const { contentsLabel, sections } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Contents', 'foundations')}>
          <TextControl
            label={__('Contents heading', 'foundations')}
            value={contentsLabel}
            onChange={(v) => setAttributes({ contentsLabel: v })}
          />
        </PanelBody>

        <Repeater
          items={sections}
          onChange={(next) => setAttributes({ sections: next })}
          blank={{ id: '', title: '', body: [], list: [] }}
          label={(item, i) => item.title || __('Section', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add section', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Title', 'foundations')}
                value={item.title || ''}
                onChange={(title) => update({ title })}
              />
              <TextControl
                label={__('Anchor', 'foundations')}
                help={__('Used by the contents list. Left empty, it is made from the title.', 'foundations')}
                value={item.id || ''}
                onChange={(id) => update({ id })}
              />
              <TextareaControl
                label={__('Paragraphs', 'foundations')}
                help={__('Leave a blank line between paragraphs.', 'foundations')}
                value={(item.body || []).join('\n\n')}
                rows={8}
                onChange={(v) => update({ body: toParagraphs(v) })}
              />
              <TextareaControl
                label={__('Bullets', 'foundations')}
                help={__('One per line. Leave empty for none.', 'foundations')}
                value={(item.list || []).join('\n')}
                rows={6}
                onChange={(v) => update({ list: toLines(v) })}
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
