/**
 * Sidebar controls for foundations/template-library.
 */
import {
  PanelBody,
  RangeControl,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Controls({ attributes, setAttributes }) {
  const { number, label, aside, priceFrom, allLabel, showFilters, note, limit } = attributes;

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
      </PanelBody>

      <PanelBody title={__('Library', 'foundations')}>
        <ToggleControl
          label={__('Show the niche filters', 'foundations')}
          help={__(
            'Filters only appear when the templates have categories set, and more than one is in use.',
            'foundations'
          )}
          checked={showFilters}
          onChange={(v) => setAttributes({ showFilters: v })}
        />
        <TextControl
          label={__('"All" button label', 'foundations')}
          value={allLabel}
          onChange={(v) => setAttributes({ allLabel: v })}
        />
        <TextControl
          label={__('Price label', 'foundations')}
          value={priceFrom}
          onChange={(v) => setAttributes({ priceFrom: v })}
        />
        <RangeControl
          label={__('Maximum templates', 'foundations')}
          help={__('Set to the maximum to show every published template.', 'foundations')}
          value={limit < 0 ? 24 : limit}
          min={1}
          max={24}
          onChange={(v) => setAttributes({ limit: v === 24 ? -1 : v })}
        />
        <TextareaControl
          label={__('Closing note', 'foundations')}
          value={note}
          rows={5}
          onChange={(v) => setAttributes({ note: v })}
        />
      </PanelBody>
    </>
  );
}
