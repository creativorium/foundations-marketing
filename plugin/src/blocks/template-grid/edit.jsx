/**
 * Editor UI for foundations/template-grid.
 *
 * The grid reads live data from the Site Templates CPT, so the editor previews it with
 * ServerSideRender — one source of truth in render.php, rather than a JS copy of the
 * markup that drifts out of sync with the PHP.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  RangeControl,
  SelectControl,
  TextControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { number, label, aside, heading, intro, priceFrom, limit, moreText, moreUrl, compact, tone } =
    attributes;

  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
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

        <PanelBody title={__('Copy', 'foundations')}>
          <TextControl
            label={__('Heading', 'foundations')}
            value={heading}
            onChange={(v) => setAttributes({ heading: v })}
          />
          <TextareaControl
            label={__('Intro', 'foundations')}
            value={intro}
            rows={5}
            onChange={(v) => setAttributes({ intro: v })}
          />
          <TextControl
            label={__('Price label', 'foundations')}
            value={priceFrom}
            onChange={(v) => setAttributes({ priceFrom: v })}
          />
        </PanelBody>

        <PanelBody title={__('Catalogue', 'foundations')}>
          <RangeControl
            label={__('Templates to show', 'foundations')}
            help={__(
              'Pulled from published Site Templates. Manage them under Site Templates.',
              'foundations'
            )}
            value={limit}
            min={1}
            max={24}
            onChange={(v) => setAttributes({ limit: v ?? 9 })}
          />
          <SelectControl
            label={__('Background', 'foundations')}
            value={tone}
            options={[
              { label: __('Page background', 'foundations'), value: 'plain' },
              { label: __('Recessed band', 'foundations'), value: 'bg2' },
            ]}
            onChange={(v) => setAttributes({ tone: v })}
          />
          <ToggleControl
            label={__('Compact cards', 'foundations')}
            help={__(
              'Name and niche only, without the description or price — the shorter listing used on the services page.',
              'foundations'
            )}
            checked={compact}
            onChange={(v) => setAttributes({ compact: v })}
          />
          <TextControl
            label={__('Footer link label', 'foundations')}
            help={__('Leave blank to hide the browse-the-library link.', 'foundations')}
            value={moreText}
            onChange={(v) => setAttributes({ moreText: v })}
          />
          <TextControl
            label={__('Footer link URL', 'foundations')}
            value={moreUrl}
            onChange={(v) => setAttributes({ moreUrl: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ServerSideRender
          block="foundations/template-grid"
          attributes={attributes}
        />
      </div>
    </>
  );
}
