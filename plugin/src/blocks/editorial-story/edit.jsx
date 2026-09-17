import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import Repeater from '../../components/Repeater.jsx';
import metadata from './block.json';

export default function Edit({ attributes, setAttributes }) {
  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Content', 'foundations')}>
          <TextControl label={__('Section label', 'foundations')} value={attributes.eyebrow} onChange={(value) => setAttributes({ eyebrow: value })} />
          <TextareaControl label={__('Heading', 'foundations')} value={attributes.heading} onChange={(value) => setAttributes({ heading: value })} />
          <TextareaControl label={__('Introduction', 'foundations')} value={attributes.intro} onChange={(value) => setAttributes({ intro: value })} />
          <SelectControl label={__('Layout', 'foundations')} value={attributes.layout} options={[{ value: 'statement', label: __('Statement and gallery', 'foundations') }, { value: 'profile', label: __('Portrait and biography', 'foundations') }, { value: 'audience', label: __('Portrait and audience list', 'foundations') }]} onChange={(value) => setAttributes({ layout: value })} />
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
        <Repeater items={attributes.paragraphs} onChange={(items) => setAttributes({ paragraphs: items })} blank={{ text: '' }} label={(item, i) => __('Paragraph', 'foundations') + ` ${i + 1}`}>
          {(item, update) => <TextareaControl label={__('Paragraph', 'foundations')} value={item.text || ''} onChange={(text) => update({ text })} />}
        </Repeater>
        <Repeater items={attributes.items} onChange={(items) => setAttributes({ items })} blank={{"title": "", "body": ""}} label={(item, i) => item.title || item.name || `Item ${i + 1}`}>
          {(item, update) => <>
            <TextControl label={__('Statistic / audience heading', 'foundations')} value={item.title || ''} onChange={(value) => update({ title: value })} />
            <TextareaControl label={__('Label / description', 'foundations')} value={item.body || ''} onChange={(value) => update({ body: value })} />
          </>}
        </Repeater>
        <Repeater items={attributes.gallery} onChange={(gallery) => setAttributes({ gallery })} blank={{ mediaId: 0, mediaAlt: '', fallbackImage: '' }} label={(item, i) => __('Gallery photo', 'foundations') + ` ${i + 1}`}>
          {(item, update) => <>
            <MediaUploadCheck><MediaUpload allowedTypes={['image']} value={item.mediaId}
              onSelect={(image) => update({ mediaId: image.id, mediaAlt: image.alt || '' })}
              render={({ open }) => <Button variant="secondary" onClick={open}>{__('Choose photo', 'foundations')}</Button>} /></MediaUploadCheck>
            {!!item.mediaId && <Button variant="tertiary" onClick={() => update({ mediaId: 0 })}>{__('Use template photo', 'foundations')}</Button>}
            <TextControl label={__('Alt text', 'foundations')} value={item.mediaAlt || ''} onChange={(mediaAlt) => update({ mediaAlt })} />
            <TextControl label={__('Pack image path', 'foundations')} value={item.fallbackImage || ''} onChange={(fallbackImage) => update({ fallbackImage })} />
          </>}
        </Repeater>
      </InspectorControls>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
