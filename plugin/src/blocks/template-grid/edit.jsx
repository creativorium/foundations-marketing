/**
 * Editor UI for foundations/template-grid.
 *
 * The grid reads live data from the Site Templates CPT, so the editor previews it with
 * ServerSideRender — one source of truth in render.php, rather than a JS copy of the
 * markup that drifts out of sync with the PHP.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { number, label, aside, heading, intro, priceFrom, limit } = attributes;

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
              'Pulled from published Site Templates, newest ordering first. Manage them under Site Templates.',
              'foundations'
            )}
            value={limit}
            min={1}
            max={24}
            onChange={(v) => setAttributes({ limit: v ?? 9 })}
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
