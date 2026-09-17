/**
 * Editor UI for foundations/halo-hero.
 *
 * The preview is ServerSideRender, so the editor shows what render.php actually emits.
 * A second copy of the markup in JSX drifts out of step with the PHP the moment either
 * is touched, and the drift is invisible until a customer opens the page.
 */
import {
  InspectorControls,
  MediaUpload,
  MediaUploadCheck,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  Button,
  Notice,
  PanelBody,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

// A list of plain strings edits as lines, not as a Repeater: Repeater items are objects
// (it spreads them), and four short credentials are quicker to edit as four lines.
const toLines = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);

export default function Edit({ attributes, setAttributes }) {
  const {
    eyebrow,
    heading,
    highlight,
    headingTail,
    lead,
    ctaLabel,
    ctaUrl,
    altLabel,
    altUrl,
    stats,
    imageId,
    imageAlt,
    imageNote,
    badgeLabel,
    badgeText,
    credentials,
  } = attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Headline', 'foundations')}>
          <TextControl
            label={__('Eyebrow', 'foundations')}
            value={eyebrow}
            onChange={(v) => setAttributes({ eyebrow: v })}
          />
          <TextareaControl
            label={__('Heading', 'foundations')}
            help={__('This is the page H1. Only one per page.', 'foundations')}
            value={heading}
            rows={2}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextControl
            label={__('Italic phrase', 'foundations')}
            help={__('Set in italic serif, inside the same H1.', 'foundations')}
            value={highlight}
            onChange={(v) => setAttributes({ highlight: v })}
          />
          <TextControl
            label={__('Closing words', 'foundations')}
            help={__('What follows the italic phrase — "again." in the original.', 'foundations')}
            value={headingTail}
            onChange={(v) => setAttributes({ headingTail: v })}
          />
          <TextareaControl
            label={__('Standfirst', 'foundations')}
            value={lead}
            rows={4}
            onChange={(v) => setAttributes({ lead: v })}
          />
        </PanelBody>

        <PanelBody title={__('Actions', 'foundations')}>
          <TextControl
            label={__('Button text', 'foundations')}
            value={ctaLabel}
            onChange={(v) => setAttributes({ ctaLabel: v })}
          />
          <TextControl
            label={__('Button URL', 'foundations')}
            value={ctaUrl}
            onChange={(v) => setAttributes({ ctaUrl: v })}
          />
          {ctaLabel.trim() !== '' && ctaUrl.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('The button has text but no destination, so it is not rendered.', 'foundations')}
            </Notice>
          )}
          <TextControl
            label={__('Second link text', 'foundations')}
            value={altLabel}
            onChange={(v) => setAttributes({ altLabel: v })}
          />
          <TextControl
            label={__('Second link URL', 'foundations')}
            value={altUrl}
            onChange={(v) => setAttributes({ altUrl: v })}
          />
        </PanelBody>

        <PanelBody title={__('Studio photograph', 'foundations')} initialOpen={false}>
          <ToggleControl
            label={__('Show design placeholder', 'foundations')}
            checked={!!attributes.placeholder}
            onChange={(placeholder) => setAttributes({ placeholder })}
            help={__('Turn off after choosing the customer’s photograph.', 'foundations')}
          />

          <MediaUploadCheck>
            <MediaUpload
              allowedTypes={['image']}
              value={imageId}
              onSelect={(media) =>
                setAttributes({ imageId: media.id, imageAlt: media.alt || imageAlt })
              }
              render={({ open }) => (
                <Button variant="secondary" onClick={open}>
                  {imageId ? __('Replace image', 'foundations') : __('Choose image', 'foundations')}
                </Button>
              )}
            />
          </MediaUploadCheck>

          {imageId > 0 && (
            <Button
              variant="link"
              isDestructive
              style={{ marginTop: 8 }}
              onClick={() => setAttributes({ imageId: 0 })}
            >
              {__('Remove image', 'foundations')}
            </Button>
          )}

          <TextControl
            label={__('Alt text', 'foundations')}
            help={__('Describe the photo and carry the page phrase.', 'foundations')}
            value={imageAlt}
            onChange={(v) => setAttributes({ imageAlt: v })}
          />

          {imageId > 0 && imageAlt.trim() === '' && (
            <Notice status="warning" isDismissible={false}>
              {__('This image has no alt text.', 'foundations')}
            </Notice>
          )}

          <TextareaControl
            label={__('Briefing note', 'foundations')}
            help={__(
              'Describes the photo still to be sourced. Shown only while the slot is empty — it is not alt text.',
              'foundations'
            )}
            value={imageNote}
            rows={2}
            onChange={(v) => setAttributes({ imageNote: v })}
          />
        </PanelBody>

        <PanelBody title={__('Booking note', 'foundations')} initialOpen={false}>
          <TextControl
            label={__('Label', 'foundations')}
            value={badgeLabel}
            onChange={(v) => setAttributes({ badgeLabel: v })}
          />
          <TextControl
            label={__('Note', 'foundations')}
            help={__('The offer floating over the photograph.', 'foundations')}
            value={badgeText}
            onChange={(v) => setAttributes({ badgeText: v })}
          />
        </PanelBody>

        <Repeater
          items={stats}
          onChange={(next) => setAttributes({ stats: next })}
          blank={{ v: '', k: '' }}
          label={(item, i) => item.v || __('Figure', 'foundations') + ` ${i + 1}`}
          addLabel={__('Add figure', 'foundations')}
        >
          {(item, update) => (
            <>
              <TextControl
                label={__('Figure', 'foundations')}
                value={item.v || ''}
                onChange={(v) => update({ v })}
              />
              <TextControl
                label={__('Label', 'foundations')}
                value={item.k || ''}
                onChange={(v) => update({ k: v })}
              />
            </>
          )}
        </Repeater>

        <PanelBody title={__('Credentials strip', 'foundations')} initialOpen={false}>
          <TextareaControl
            label={__('Credentials', 'foundations')}
            help={__('One per line. Leave empty to drop the strip.', 'foundations')}
            value={credentials.join('\n')}
            rows={5}
            onChange={(v) => setAttributes({ credentials: toLines(v) })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    </>
  );
}
