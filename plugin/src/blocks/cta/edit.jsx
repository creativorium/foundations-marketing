/**
 * Editor UI for foundations/cta.
 */
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Never H1 — the hero owns that.
const LEVELS = [2, 3].map((n) => ({ label: `H${n}`, value: n }));

export default function Edit({ attributes, setAttributes }) {
  const {
    heading,
    headingAccent,
    lede,
    primaryText,
    primaryUrl,
    secondaryText,
    secondaryUrl,
    footnote,
    level,
  } = attributes;

  const blockProps = useBlockProps({ className: 'fm-cta' });
  const HeadingTag = `h${level}`;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Heading', 'foundations')}>
          <SelectControl
            label={__('Heading level', 'foundations')}
            help={__('The hero owns the page H1, so this stays H2 or lower.', 'foundations')}
            value={level}
            options={LEVELS}
            onChange={(v) => setAttributes({ level: Number(v) })}
          />
          <TextareaControl
            label={__('Lede', 'foundations')}
            value={lede}
            rows={3}
            onChange={(v) => setAttributes({ lede: v })}
          />
        </PanelBody>

        <PanelBody title={__('Buttons', 'foundations')}>
          <TextControl
            label={__('Primary label', 'foundations')}
            value={primaryText}
            onChange={(v) => setAttributes({ primaryText: v })}
          />
          <TextControl
            label={__('Primary link', 'foundations')}
            value={primaryUrl}
            onChange={(v) => setAttributes({ primaryUrl: v })}
          />
          <TextControl
            label={__('Secondary label', 'foundations')}
            value={secondaryText}
            onChange={(v) => setAttributes({ secondaryText: v })}
          />
          <TextControl
            label={__('Secondary link', 'foundations')}
            value={secondaryUrl}
            onChange={(v) => setAttributes({ secondaryUrl: v })}
          />
          <TextControl
            label={__('Footnote', 'foundations')}
            value={footnote}
            onChange={(v) => setAttributes({ footnote: v })}
          />
        </PanelBody>
      </InspectorControls>

      <section {...blockProps}>
        <HeadingTag className="fm-cta__heading">
          <RichText
            tagName="span"
            allowedFormats={[]}
            value={heading}
            placeholder={__('Closing headline', 'foundations')}
            onChange={(v) => setAttributes({ heading: v })}
          />{' '}
          <RichText
            tagName="span"
            className="fm-cta__heading-accent"
            allowedFormats={[]}
            value={headingAccent}
            placeholder={__('accent word', 'foundations')}
            onChange={(v) => setAttributes({ headingAccent: v })}
          />
        </HeadingTag>

        {lede && <p className="fm-cta__lede">{lede}</p>}

        <p className="fm-cta__actions">
          {primaryText && <span className="fm-cta__button">{primaryText} &rarr;</span>}
          {secondaryText && <span className="fm-cta__link">{secondaryText}</span>}
        </p>

        {footnote && <p className="fm-cta__footnote">{footnote}</p>}
      </section>
    </>
  );
}
