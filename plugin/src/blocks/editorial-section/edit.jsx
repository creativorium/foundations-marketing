import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { eyebrow, heading, intro, tone, layout } = attributes;
  return <>
    <InspectorControls><PanelBody title={__('Section', 'foundations')}>
      <TextControl label={__('Section label', 'foundations')} value={eyebrow} onChange={(eyebrow) => setAttributes({ eyebrow })} />
      <TextareaControl label={__('Heading', 'foundations')} value={heading} onChange={(heading) => setAttributes({ heading })} />
      <TextareaControl label={__('Introduction', 'foundations')} value={intro} onChange={(intro) => setAttributes({ intro })} />
      <SelectControl label={__('Layout', 'foundations')} value={layout} onChange={(layout) => setAttributes({ layout })}
        options={[{ value: 'stack', label: __('Stacked', 'foundations') }, { value: 'split', label: __('Split', 'foundations') }]} />
      <SelectControl label={__('Tone', 'foundations')} value={tone} onChange={(tone) => setAttributes({ tone })}
        options={[{ value: 'paper', label: __('Paper', 'foundations') }, { value: 'panel', label: __('Recessed', 'foundations') }, { value: 'accent', label: __('Accent', 'foundations') }]} />
    </PanelBody></InspectorControls>
    <section {...useBlockProps({ className: `fm-editorial-section fm-editorial-section--${tone} fm-editorial-section--${layout}` })}>
      <div className="fm-editorial-section__intro">
        {eyebrow && <p className="fm-editorial-section__eyebrow">{eyebrow}</p>}
        {heading && <h2 className="fm-editorial-section__heading">{heading}</h2>}
        {intro && <p>{intro}</p>}
      </div>
      <div className="fm-editorial-section__content"><InnerBlocks allowedBlocks={['core/details', 'core/paragraph', 'core/buttons', 'core/heading', 'core/list']} /></div>
    </section>
  </>;
}
export function Save() { return <InnerBlocks.Content />; }
