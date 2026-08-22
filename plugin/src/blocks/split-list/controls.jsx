/**
 * Sidebar controls for foundations/split-list.
 */
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/** Both columns are simple string lists, edited one item per line. */
const toLines = (items = []) =>
  items.map((i) => (typeof i === 'string' ? i : i.text || '')).join('\n');

const fromLines = (value) =>
  value
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((text) => ({ text }));

export default function Controls({ attributes, setAttributes }) {
  const {
    number,
    label,
    aside,
    heading,
    intro,
    leftEyebrow,
    leftTitle,
    leftItems,
    rightEyebrow,
    rightTitle,
    rightItems,
    rightNote,
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
      </PanelBody>

      <PanelBody title={__('Left card — our job', 'foundations')}>
        <TextControl
          label={__('Eyebrow', 'foundations')}
          value={leftEyebrow}
          onChange={(v) => setAttributes({ leftEyebrow: v })}
        />
        <TextControl
          label={__('Title', 'foundations')}
          value={leftTitle}
          onChange={(v) => setAttributes({ leftTitle: v })}
        />
        <TextareaControl
          label={__('Items, one per line', 'foundations')}
          value={toLines(leftItems)}
          rows={9}
          onChange={(v) => setAttributes({ leftItems: fromLines(v) })}
        />
      </PanelBody>

      <PanelBody title={__('Right card — your part', 'foundations')}>
        <TextControl
          label={__('Eyebrow', 'foundations')}
          value={rightEyebrow}
          onChange={(v) => setAttributes({ rightEyebrow: v })}
        />
        <TextControl
          label={__('Title', 'foundations')}
          value={rightTitle}
          onChange={(v) => setAttributes({ rightTitle: v })}
        />
        <TextareaControl
          label={__('Items, one per line', 'foundations')}
          value={toLines(rightItems)}
          rows={7}
          onChange={(v) => setAttributes({ rightItems: fromLines(v) })}
        />
        <TextareaControl
          label={__('Note', 'foundations')}
          help={__('The reassurance line, set in the serif.', 'foundations')}
          value={rightNote}
          rows={4}
          onChange={(v) => setAttributes({ rightNote: v })}
        />
      </PanelBody>
    </>
  );
}
