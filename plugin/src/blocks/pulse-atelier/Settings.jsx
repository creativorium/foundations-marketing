import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import Repeater from '../../components/Repeater.jsx';

const label = key => key.replaceAll('_', ' ').replace(/^./, letter => letter.toUpperCase());
function Record({ schema, value, onChange }) {
  return Object.entries(schema).map(([key, fallback]) => Array.isArray(fallback)
    ? <PanelBody key={key} title={label(key)} initialOpen={false}>
      <Repeater items={value[key] ?? fallback} blank={fallback[0] ?? {}} onChange={next => onChange({ ...value, [key]: next })}>
        {(item, update) => <Record schema={fallback[0] ?? {}} value={item} onChange={update} />}
      </Repeater>
    </PanelBody>
    : <TextareaControl key={key} label={label(key)} value={value[key] ?? fallback} onChange={next => onChange({ ...value, [key]: next })} />);
}
export default function Settings({ defaults, value = {}, onChange }) {
  const change = (group, next) => onChange({ ...value, [group]: next });
  return <>
    <PanelBody title="Text and links" initialOpen={false}>
      <Record schema={defaults.fields} value={value.fields ?? {}} onChange={next => change('fields', next)} />
    </PanelBody>
    {Object.entries(defaults.lists).map(([key, rows]) => <PanelBody key={key} title={label(key)} initialOpen={false}>
      <Repeater items={value.lists?.[key] ?? rows} blank={rows[0] ?? {}} label={(item, index) => item.name || item.title || item.day || item.author || item.q || `Item ${index + 1}`} onChange={next => change('lists', { ...value.lists, [key]: next })}>
        {(item, update) => <Record schema={rows[0] ?? {}} value={item} onChange={update} />}
      </Repeater>
    </PanelBody>)}
    {Object.keys(defaults.images).length > 0 && <PanelBody title="Photography" initialOpen={false}>
      {Object.keys(defaults.images).map(key => <div key={key}>
        <TextControl label={label(key) + ' image URL (empty uses supplied image)'} value={value.images?.[key] ?? ''} onChange={url => change('images', { ...value.images, [key]: url })} />
        <MediaUploadCheck><MediaUpload allowedTypes={['image']} onSelect={media => change('images', { ...value.images, [key]: media.url })} render={({ open }) => <Button variant="secondary" onClick={open}>Choose {label(key)}</Button>} /></MediaUploadCheck>
      </div>)}
    </PanelBody>}
  </>;
}
