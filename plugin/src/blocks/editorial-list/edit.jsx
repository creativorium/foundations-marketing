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
          <SelectControl label={__('Layout', 'foundations')} value={attributes.layout} options={[{ value: 'credentials', label: __('Credentials strip', 'foundations') }, { value: 'services', label: __('Sessions and prices', 'foundations') }, { value: 'process', label: __('Numbered process', 'foundations') }]} onChange={(value) => setAttributes({ layout: value })} />
        </PanelBody>
        <Repeater items={attributes.items} onChange={(items) => setAttributes({ items })} blank={{"title": "", "body": "", "duration": "", "price": "", "url": ""}} label={(item, i) => item.title || item.name || `Item ${i + 1}`}>
          {(item, update) => <>
            <TextControl label={__('Title', 'foundations')} value={item.title || ''} onChange={(value) => update({ title: value })} />
            <TextareaControl label={__('Description', 'foundations')} value={item.body || ''} onChange={(value) => update({ body: value })} />
            <TextControl label={__('Duration (sessions)', 'foundations')} value={item.duration || ''} onChange={(value) => update({ duration: value })} />
            <TextControl label={__('Price (sessions)', 'foundations')} value={item.price || ''} onChange={(value) => update({ price: value })} />
            <TextControl label={__('Link (optional)', 'foundations')} value={item.url || ''} onChange={(value) => update({ url: value })} />
          </>}
        </Repeater>
      </InspectorControls>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
