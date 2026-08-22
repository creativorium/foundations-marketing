/**
 * Sidebar controls for foundations/page-hero.
 */
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

export default function Controls({ attributes, setAttributes }) {
  const {
    badge,
    note,
    heading,
    headingAccent,
    lede,
    cardTitle,
    rows,
    ctaText,
    ctaUrl,
  } = attributes;

  return (
    <>
      <PanelBody title={__('Headline', 'foundations')}>
        <TextControl
          label={__('Badge', 'foundations')}
          value={badge}
          onChange={(v) => setAttributes({ badge: v })}
        />
        <TextControl
          label={__('Note', 'foundations')}
          value={note}
          onChange={(v) => setAttributes({ note: v })}
        />
        <TextControl
          label={__('Heading', 'foundations')}
          help={__('This is the page H1. Only one hero per page.', 'foundations')}
          value={heading}
          onChange={(v) => setAttributes({ heading: v })}
        />
        <TextControl
          label={__('Accent line', 'foundations')}
          value={headingAccent}
          onChange={(v) => setAttributes({ headingAccent: v })}
        />
        <TextareaControl
          label={__('Lede', 'foundations')}
          value={lede}
          rows={5}
          onChange={(v) => setAttributes({ lede: v })}
        />
      </PanelBody>

      <PanelBody title={__('At-a-glance card', 'foundations')}>
        <TextControl
          label={__('Card title', 'foundations')}
          value={cardTitle}
          onChange={(v) => setAttributes({ cardTitle: v })}
        />
        <TextControl
          label={__('Button label', 'foundations')}
          value={ctaText}
          onChange={(v) => setAttributes({ ctaText: v })}
        />
        <TextControl
          label={__('Button link', 'foundations')}
          value={ctaUrl}
          onChange={(v) => setAttributes({ ctaUrl: v })}
        />
      </PanelBody>

      <Repeater
        items={rows}
        onChange={(next) => setAttributes({ rows: next })}
        blank={{ k: '', v: '' }}
        label={(item, i) => item.k || __('Row', 'foundations') + ` ${i + 1}`}
        addLabel={__('Add row', 'foundations')}
      >
        {(item, update) => (
          <>
            <TextControl
              label={__('Label', 'foundations')}
              value={item.k || ''}
              onChange={(v) => update({ k: v })}
            />
            <TextControl
              label={__('Value', 'foundations')}
              value={item.v || ''}
              onChange={(v) => update({ v })}
            />
          </>
        )}
      </Repeater>
    </>
  );
}
