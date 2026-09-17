import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  const h1Count = useSelect((select) => {
    const count = (blocks) => blocks.reduce((total, block) => {
      const ownsH1 = ['foundations/hero', 'foundations/page-hero'].includes(block.name)
        || (block.name === 'foundations/editorial-hero' && block.attributes.layout !== 'banner')
        || (block.name === 'core/heading' && block.attributes.level === 1);
      return total + Number(ownsH1) + count(block.innerBlocks || []);
    }, 0);
    return count(select('core/block-editor').getBlocks());
  }, []);
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        {attributes.layout !== 'banner' && h1Count > 1 && <Notice status="warning" isDismissible={false}>
          {__('This page has multiple H1 headings. Keep one split hero; use a photo banner for an additional offer.', 'foundations')}
        </Notice>}
        <PanelBody title={__('Content', 'foundations')}>
          <TextControl label={__('Section label', 'foundations')} value={attributes.eyebrow} onChange={(value) => setAttributes({ eyebrow: value })} />
          <TextareaControl label={__('Heading', 'foundations')} value={attributes.heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextareaControl label={__('Introduction', 'foundations')} value={attributes.intro} onChange={(value) => setAttributes({ intro: value })} />
          <TextControl label={__('Italic heading text', 'foundations')} value={attributes.accentText} onChange={(value) => setAttributes({ accentText: value })} />
          <TextControl label={__('Heading ending', 'foundations')} value={attributes.headingEnd} onChange={(value) => setAttributes({ headingEnd: value })} />
          <SelectControl label={__('Layout', 'foundations')} value={attributes.layout} options={[{ value: 'split', label: __('Split photo / H1', 'foundations') }, { value: 'banner', label: __('Photo banner / H2', 'foundations') }]} onChange={(value) => setAttributes({ layout: value })} />
          <TextControl label={__('Primary link text', 'foundations')} value={attributes.primaryText} onChange={(value) => setAttributes({ primaryText: value })} />
          <TextControl label={__('Primary URL (enquiry, booking or section anchor)', 'foundations')} value={attributes.primaryUrl} onChange={(value) => setAttributes({ primaryUrl: value })} />
          <TextControl label={__('Secondary link text', 'foundations')} value={attributes.secondaryText} onChange={(value) => setAttributes({ secondaryText: value })} />
          <TextControl label={__('Secondary URL', 'foundations')} value={attributes.secondaryUrl} onChange={(value) => setAttributes({ secondaryUrl: value })} />
          <TextareaControl label={__('Footnote', 'foundations')} value={attributes.footnote} onChange={(value) => setAttributes({ footnote: value })} />
        </PanelBody>
        <PanelBody title={__('Photo', 'foundations')} initialOpen={false}>
          <MediaUploadCheck>
            <MediaUpload allowedTypes={['image']} value={attributes.mediaId}
              onSelect={(image) => setAttributes({ mediaId: image.id, mediaAlt: image.alt || '' })}
              render={({ open }) => <Button variant="secondary" onClick={open}>{__('Choose / replace photo', 'foundations')}</Button>} />
          </MediaUploadCheck>
          {!!attributes.mediaId && <Button variant="tertiary" onClick={() => setAttributes({ mediaId: 0 })}>{__('Use template photo', 'foundations')}</Button>}
          <TextControl label={__('Alt text', 'foundations')} value={attributes.mediaAlt} onChange={(value) => setAttributes({ mediaAlt: value })} />
          <TextControl label={__('Pack image path', 'foundations')} help={__('Relative to templates/, for example pulse-atelier/assets/hero.webp. Used when no media image is selected.', 'foundations')} value={attributes.fallbackImage} onChange={(value) => setAttributes({ fallbackImage: value })} />
          {!attributes.mediaAlt && <Notice status="warning" isDismissible={false}>{__('Add alt text unless the image is decorative.', 'foundations')}</Notice>}
        </PanelBody>
      </InspectorControls>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
