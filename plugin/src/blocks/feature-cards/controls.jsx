/**
 * Sidebar controls for foundations/feature-cards.
 */
import {
  PanelBody,
  SelectControl,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';

const TONES = [
  { label: __('Warm blue band', 'foundations'), value: 'warm' },
  { label: __('Page background', 'foundations'), value: 'plain' },
];

export default function Controls({ attributes, setAttributes }) {
  const {
    number,
    label,
    aside,
    heading,
    intro,
    tone,
    items,
    showPrice,
    pricePrefix,
    price,
    priceNote,
    ctaText,
    ctaUrl,
  } = attributes;

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
        <SelectControl
          label={__('Background', 'foundations')}
          value={tone}
          options={TONES}
          onChange={(v) => setAttributes({ tone: v })}
        />
      </PanelBody>

      <PanelBody title={__('Price bar', 'foundations')}>
        <ToggleControl
          label={__('Show the price bar', 'foundations')}
          checked={showPrice}
          onChange={(v) => setAttributes({ showPrice: v })}
        />
        {showPrice && (
          <>
            <TextControl
              label={__('Prefix', 'foundations')}
              value={pricePrefix}
              onChange={(v) => setAttributes({ pricePrefix: v })}
            />
            <TextControl
              label={__('Price', 'foundations')}
              value={price}
              onChange={(v) => setAttributes({ price: v })}
            />
            <TextareaControl
              label={__('Note', 'foundations')}
              value={priceNote}
              rows={3}
              onChange={(v) => setAttributes({ priceNote: v })}
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
          </>
        )}
      </PanelBody>

      <Repeater
        items={items}
        onChange={(next) => setAttributes({ items: next })}
        blank={{ n: '', title: '', body: '' }}
        label={(item, i) => item.title || __('Feature', 'foundations') + ` ${i + 1}`}
        addLabel={__('Add feature', 'foundations')}
      >
        {(item, update) => (
          <>
            <TextControl
              label={__('Number', 'foundations')}
              value={item.n || ''}
              onChange={(v) => update({ n: v })}
            />
            <TextControl
              label={__('Title', 'foundations')}
              value={item.title || ''}
              onChange={(v) => update({ title: v })}
            />
            <TextareaControl
              label={__('Body', 'foundations')}
              value={item.body || ''}
              rows={4}
              onChange={(v) => update({ body: v })}
            />
          </>
        )}
      </Repeater>
    </>
  );
}
