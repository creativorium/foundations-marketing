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
        </PanelBody>
        <Repeater items={attributes.items} onChange={(items) => setAttributes({ items })} blank={{"text": "", "name": ""}} label={(item, i) => item.title || item.name || `Item ${i + 1}`}>
          {(item, update) => <>
            <TextareaControl label={__('Quote', 'foundations')} value={item.text || ''} onChange={(value) => update({ text: value })} />
            <TextControl label={__('Attribution', 'foundations')} value={item.name || ''} onChange={(value) => update({ name: value })} />
          </>}
        </Repeater>
      </InspectorControls>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
