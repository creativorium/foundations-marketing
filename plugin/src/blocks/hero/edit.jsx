/**
 * Editor UI for foundations/hero.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  RichText,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  Button,
  PanelBody,
  SelectControl,
  TextControl,
  Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const VARIANTS = [
  { label: __('A — split (copy left, device right)', 'foundations'), value: 'a' },
  { label: __('B — centred, oversized heading', 'foundations'), value: 'b' },
];

export default function Edit({ attributes, setAttributes }) {
  const {
    variant,
    badge,
    note,
    heading,
    headingAccent,
    lede,
    primaryText,
    primaryUrl,
    secondaryText,
    secondaryUrl,
    footnote,
    mediaId,
    mediaAlt,
    captionLeft,
    captionRight,
  } = attributes;

  const blockProps = useBlockProps({
    className: `fm-hero fm-hero--${variant}`,
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Layout', 'foundations')}>
          <SelectControl
            label={__('Variant', 'foundations')}
            value={variant}
            options={VARIANTS}
            onChange={(v) => setAttributes({ variant: v })}
          />
        </PanelBody>

        <PanelBody title={__('Flags', 'foundations')}>
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

        <PanelBody title={__('Screenshot', 'foundations')}>
          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              value={mediaId}
              onSelect={(media) =>
                setAttributes({
                  mediaId: media.id,
                  // Reuse the alt already set in the media library if there is one.
                  mediaAlt: media.alt || mediaAlt,
                })
              }
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {mediaId
                    ? __('Replace screenshot', 'foundations')
                    : __('Choose screenshot', 'foundations')}
                </Button>
              )}
            />
          </MediaUploadCheck>

          {mediaId > 0 && (
            <Button
              variant="link"
              isDestructive
              onClick={() => setAttributes({ mediaId: 0 })}
              style={{ marginTop: 8 }}
            >
              {__('Remove screenshot', 'foundations')}
            </Button>
          )}

          <TextControl
            label={__('Alt text', 'foundations')}
            help={__(
              'Describe it and include the page target phrase, e.g. "Pilates studio website template by Foundations Marketing".',
              'foundations'
            )}
            value={mediaAlt}
            onChange={(v) => setAttributes({ mediaAlt: v })}
          />

          {mediaId > 0 && mediaAlt.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('This image has no alt text. Every image needs one.', 'foundations')}
            </Notice>
          )}

          <TextControl
            label={__('Caption left', 'foundations')}
            value={captionLeft}
            onChange={(v) => setAttributes({ captionLeft: v })}
          />
          <TextControl
            label={__('Caption right', 'foundations')}
            value={captionRight}
            onChange={(v) => setAttributes({ captionRight: v })}
          />
        </PanelBody>
      </InspectorControls>

      <section {...blockProps}>
        <div className="fm-hero__inner">
          <div className="fm-hero__copy">
            {(badge || note) && (
              <p className="fm-hero__flags">
                {badge && <span className="fm-hero__badge">{badge}</span>}
                {note && <span className="fm-hero__note">{note}</span>}
              </p>
            )}

            <h1 className="fm-hero__title">
              <RichText
                tagName="span"
                allowedFormats={[]}
                value={heading}
                placeholder={__('Pick a template.', 'foundations')}
                onChange={(v) => setAttributes({ heading: v })}
              />
              <RichText
                tagName="span"
                className="fm-hero__title-accent"
                allowedFormats={[]}
                value={headingAccent}
                placeholder={__('Go live Friday.', 'foundations')}
                onChange={(v) => setAttributes({ headingAccent: v })}
              />
            </h1>

            <RichText
              tagName="p"
              className="fm-hero__lede"
              allowedFormats={[]}
              value={lede}
              placeholder={__('One or two sentences of supporting copy.', 'foundations')}
              onChange={(v) => setAttributes({ lede: v })}
            />

            <p className="fm-hero__actions">
              {primaryText && <span className="fm-hero__cta">{primaryText} &rarr;</span>}
              {secondaryText && <span className="fm-hero__link">{secondaryText}</span>}
            </p>

            {footnote && <p className="fm-hero__footnote">{footnote}</p>}
          </div>

          <div className="fm-hero__media">
            <div className="fm-hero__device">
              <span className="fm-hero__device-dot" />
              <div className="fm-hero__screen">
                {mediaId > 0 ? (
                  <span className="fm-hero__shot-placeholder">
                    {__('Screenshot set', 'foundations')}
                  </span>
                ) : (
                  <span className="fm-hero__shot-placeholder">
                    {__('No screenshot chosen', 'foundations')}
                  </span>
                )}
              </div>
            </div>

            {(captionLeft || captionRight) && (
              <p className="fm-hero__caption">
                <span>{captionLeft}</span>
                <span className="fm-hero__caption-accent">{captionRight}</span>
              </p>
            )}
          </div>
        </div>
      </section>
    </>
  );
}
