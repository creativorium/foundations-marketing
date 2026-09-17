/**
 * Editor UI for foundations/onyx-policy.
 *
 * There is no control for the contents list — render.php derives it from the sections.
 * Offering an editable copy would let the two drift apart, which is the bug this block's
 * shape exists to prevent.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

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
        <PanelBody title={__('Contents list', 'foundations')}>
          <TextControl
            label={__('Heading', 'foundations')}
            help={__('The list itself is built from the sections below.', 'foundations')}
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
                onChange={(v) => update({ title: v })}
              />
              <TextareaControl
                label={__('Paragraphs', 'foundations')}
                help={__('One paragraph per line.', 'foundations')}
                value={(item.body || []).join('\n')}
                rows={5}
                onChange={(v) => update({ body: toLines(v) })}
              />
              <TextareaControl
                label={__('Bulleted points', 'foundations')}
                help={__('One per line. Leave empty for none.', 'foundations')}
                value={(item.list || []).join('\n')}
                rows={5}
                onChange={(v) => update({ list: toLines(v) })}
              />
              <TextControl
                label={__('Anchor', 'foundations')}
                help={__('Optional. Derived from the title when left empty.', 'foundations')}
                value={item.id || ''}
                onChange={(v) => update({ id: v })}
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
