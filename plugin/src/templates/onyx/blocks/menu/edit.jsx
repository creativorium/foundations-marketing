/**
 * Editor UI for foundations/onyx-menu.
 *
 * The data is two levels deep — groups, each holding treatments — so the Repeater is
 * nested inside itself. The alternative, one flat list with a "group" field repeated on
 * every row, makes reordering a group mean editing every treatment in it.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const { groups, footNote, ctaLabel, ctaUrl } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <Repeater
          items={groups}
          onChange={(next) => setAttributes({ groups: next })}
          blank={{ num: '', group: '', items: [] }}
          label={(item, i) => item.group || __('Group', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add group', 'foundations')}
        >
          {(group, updateGroup) => (
            <>
              <TextControl
                label={__('Number', 'foundations')}
                value={group.num || ''}
                onChange={(v) => updateGroup({ num: v })}
              />
              <TextControl
                label={__('Group name', 'foundations')}
                value={group.group || ''}
                onChange={(v) => updateGroup({ group: v })}
              />

              <Repeater
                items={group.items || []}
                onChange={(next) => updateGroup({ items: next })}
                blank={{ name: '', desc: '', time: '', price: '' }}
                label={(item, i) => item.name || __('Treatment', 'foundations') + ` ${i + 1}`}
                addLabel={__('Add treatment', 'foundations')}
              >
                {(item, updateItem) => (
                  <>
                    <TextControl
                      label={__('Name', 'foundations')}
                      value={item.name || ''}
                      onChange={(v) => updateItem({ name: v })}
                    />
                    <TextareaControl
                      label={__('Description', 'foundations')}
                      value={item.desc || ''}
                      rows={3}
                      onChange={(v) => updateItem({ desc: v })}
                    />
                    <TextControl
                      label={__('Duration', 'foundations')}
                      value={item.time || ''}
                      onChange={(v) => updateItem({ time: v })}
                    />
                    <TextControl
                      label={__('Price', 'foundations')}
                      value={item.price || ''}
                      onChange={(v) => updateItem({ price: v })}
                    />
                  </>
                )}
              </Repeater>
            </>
          )}
        </Repeater>

        <PanelBody title={__('Closing prompt', 'foundations')} initialOpen={false}>
          <TextareaControl
            label={__('Note', 'foundations')}
            value={footNote}
            rows={3}
            onChange={(v) => setAttributes({ footNote: v })}
          />
          <TextControl
            label={__('Button text', 'foundations')}
            value={ctaLabel}
            onChange={(v) => setAttributes({ ctaLabel: v })}
          />
          <TextControl
            label={__('Button link', 'foundations')}
            value={ctaUrl}
            onChange={(v) => setAttributes({ ctaUrl: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
