/**
 * Editor UI for foundations/section-heading.
 *
 * JSX lives in .jsx files — Vite only applies the JSX transform to that extension.
 * Registration stays in index.js so the block's entry point is plain JS.
 */
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const TONES = [
  { label: __('Page background', 'foundations'), value: 'bg' },
  { label: __('Recessed band', 'foundations'), value: 'bg2' },
  { label: __('Accent band', 'foundations'), value: 'band' },
];

const LEVELS = [2, 3, 4].map((n) => ({ label: `H${n}`, value: n }));

export default function Edit({ attributes, setAttributes }) {
    const { number, label, aside, heading, highlight, level, tone } = attributes;

    const blockProps = useBlockProps({
      className: `fm-section-heading fm-section-heading--${tone}`,
    });

    // One H1 per page (SEO strategy §4) — the hero owns it, so warn if this block
    // is set to H1 while another heading block on the page already claims one.
    const h1Count = useSelect(
      (select) =>
        select('core/block-editor')
          .getBlocks()
          .filter((b) => b.attributes?.level === 1).length,
      []
    );

    const HeadingTag = `h${level}`;

    return (
      <>
        <InspectorControls>
          <PanelBody title={__('Section rule', 'foundations')}>
            <TextControl
              label={__('Number', 'foundations')}
              help={__('The "06" in "06 — Questions".', 'foundations')}
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
              help={__('Accent-coloured text on the right of the rule.', 'foundations')}
              value={aside}
              onChange={(v) => setAttributes({ aside: v })}
            />
          </PanelBody>

          <PanelBody title={__('Appearance', 'foundations')}>
            <SelectControl
              label={__('Background', 'foundations')}
              value={tone}
              options={TONES}
              onChange={(v) => setAttributes({ tone: v })}
            />
            <SelectControl
              label={__('Heading level', 'foundations')}
              value={level}
              options={LEVELS}
              onChange={(v) => setAttributes({ level: Number(v) })}
            />
            {h1Count > 1 && (
              <Notice status="warning" isDismissible={false}>
                {__(
                  'This page has more than one H1. Search engines expect exactly one — leave the H1 to the hero block.',
                  'foundations'
                )}
              </Notice>
            )}
          </PanelBody>
        </InspectorControls>

        <section {...blockProps}>
          {(number || label) && (
            <div className="fm-section-rule">
              <span>{[number, label].filter(Boolean).join(' — ')}</span>
              {aside && <span className="fm-section-rule__aside">{aside}</span>}
            </div>
          )}

          <HeadingTag className="fm-section-heading__title">
            <RichText
              tagName="span"
              allowedFormats={[]}
              value={heading}
              placeholder={__('Section heading', 'foundations')}
              onChange={(v) => setAttributes({ heading: v })}
            />{' '}
            <RichText
              tagName="span"
              className="fm-section-heading__highlight"
              allowedFormats={[]}
              value={highlight}
              placeholder={__('Accent line', 'foundations')}
              onChange={(v) => setAttributes({ highlight: v })}
            />
          </HeadingTag>
        </section>
    </>
  );
}
