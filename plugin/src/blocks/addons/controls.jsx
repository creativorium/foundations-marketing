/**
 * Sidebar controls for foundations/addons.
 */
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Controls({ attributes, setAttributes }) {
  const { number, label, aside, heading, intro, items, note } = attributes;

  return (
    <>
      <PanelBody title={__('Section rule', 'foundations')}>
        <TextControl
          label={__('Number', 'foundations')}
          value={number}
          onChange={(v) => setAttributes({ number: v })}
        />
        <TextControl
          label={__('Label', 'foundations')}
          value={label}
          onChange={(v) => setAttributes({ label: v })}
        />
        <TextControl
          label={__('Aside', 'foundations')}
          value={aside}
          onChange={(v) => setAttributes({ aside: v })}
        />
        <TextControl
          label={__('Heading', 'foundations')}
          value={heading}
          onChange={(v) => setAttributes({ heading: v })}
        />
        <TextareaControl
          label={__('Intro', 'foundations')}
          value={intro}
          rows={4}
          onChange={(v) => setAttributes({ intro: v })}
        />
        <TextareaControl
          label={__('Closing note', 'foundations')}
          value={note}
          rows={4}
          onChange={(v) => setAttributes({ note: v })}
        />
      </PanelBody>

      <Repeater
        items={items}
        onChange={(next) => setAttributes({ items: next })}
        blank={{ name: '', price: '', body: '', when: '' }}
        label={(item, i) => item.name || __('Add-on', 'foundations') + ` ${i + 1}`}
        addLabel={__('Add add-on', 'foundations')}
      >
        {(item, update) => (
          <>
            <TextControl
              label={__('Name', 'foundations')}
              value={item.name || ''}
              onChange={(v) => update({ name: v })}
            />
            <TextControl
              label={__('Price', 'foundations')}
              value={item.price || ''}
              onChange={(v) => update({ price: v })}
            />
            <TextareaControl
              label={__('What it does', 'foundations')}
              value={item.body || ''}
              rows={4}
              onChange={(v) => update({ body: v })}
            />
            <TextControl
              label={__("When it's worth it", 'foundations')}
              help={__('A short verdict, set as a label in caps.', 'foundations')}
              value={item.when || ''}
              onChange={(v) => update({ when: v })}
            />
          </>
        )}
      </Repeater>
    </>
  );
}
