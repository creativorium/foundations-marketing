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
          <TextareaControl label={__('Timetable note', 'foundations')} value={attributes.note} onChange={(value) => setAttributes({ note: value })} />
        </PanelBody>
        <Notice status="info" isDismissible={false}>{__('This is an editable timetable, not live booking availability.', 'foundations')}</Notice>
        <Repeater items={attributes.days} onChange={(days) => setAttributes({ days })} blank={{ day: '', slots: [] }} label={(item) => item.day || __('Day', 'foundations')}>
          {(item, update) => <>
            <TextControl label={__('Day', 'foundations')} value={item.day || ''} onChange={(day) => update({ day })} />
            <Repeater items={item.slots || []} onChange={(slots) => update({ slots })} blank={{ time: '', name: '' }} label={(slot) => slot.time || __('Session', 'foundations')}>
              {(slot, change) => <>
                <TextControl label={__('Time (HH:MM)', 'foundations')} value={slot.time || ''} onChange={(time) => change({ time })} />
                <TextControl label={__('Session name', 'foundations')} value={slot.name || ''} onChange={(name) => change({ name })} />
              </>}
            </Repeater>
          </>}
        </Repeater>
      </InspectorControls>
      <ServerSideRender block={metadata.name} attributes={attributes} />
    </div>
  );
}
