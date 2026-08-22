/**
 * Editor UI for foundations/marquee.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  RangeControl,
  TextareaControl,
  ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { items, speed, spacedTop } = attributes;

  const blockProps = useBlockProps({
    className: `fm-marquee${spacedTop ? ' fm-marquee--spaced' : ''}`,
  });

  const list = items
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Items', 'foundations')}>
          <TextareaControl
            label={__('One per line', 'foundations')}
            help={__(
              'Joined with an em dash on the front end. These are the niches served.',
              'foundations'
            )}
            value={items}
            rows={10}
            onChange={(v) => setAttributes({ items: v })}
          />
        </PanelBody>

        <PanelBody title={__('Motion', 'foundations')}>
          <RangeControl
            label={__('Seconds per loop', 'foundations')}
            help={__(
              'Higher is slower. Visitors who ask for reduced motion get a static strip regardless.',
              'foundations'
            )}
            value={speed}
            min={10}
            max={200}
            onChange={(v) => setAttributes({ speed: v ?? 46 })}
          />
          <ToggleControl
            label={__('Space above', 'foundations')}
            checked={spacedTop}
            onChange={(v) => setAttributes({ spacedTop: v })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="fm-marquee__track">
          <span className="fm-marquee__strip">
            {list.length
              ? list.join(' — ')
              : __('Add one item per line in the sidebar.', 'foundations')}
          </span>
        </div>
      </div>
    </>
  );
}
