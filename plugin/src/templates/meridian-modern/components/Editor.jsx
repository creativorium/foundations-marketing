import { useBlockProps, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { TextControl, TextareaControl, Button } from '@wordpress/components';

export default function Editor({ attributes, setAttributes, metadata }) {
  const label = key => key.replace(/([A-Z])/g, ' $1').replace(/^./, c => c.toUpperCase());
  return <div {...useBlockProps({ className: 'meridian-editor' })}>
    <h3>{metadata.title}</h3>
    {!Object.keys(metadata.attributes).length && <p>Edit business details, navigation and links in Site Settings.</p>}
    {Object.entries(metadata.attributes).map(([key, schema]) => {
      const value = attributes[key];
      if (schema.type === 'integer') return <MediaUploadCheck key={key}><MediaUpload allowedTypes={['image']} value={value} onSelect={media => setAttributes({ [key]: media.id })} render={({ open }) => <><Button variant="secondary" onClick={open}>{label(key)}: {value ? 'Replace image' : 'Choose image'}</Button>{!!value && <Button variant="tertiary" onClick={() => setAttributes({ [key]: 0 })}>Remove image</Button>}</>} /></MediaUploadCheck>;
      if (schema.type === 'array') return <div key={key}><h4>{label(key)}</h4>{(value || []).map((item, i) => <fieldset key={i}><legend>Item {i + 1}</legend>{['title', 'body'].map(field => <TextareaControl key={field} label={label(field)} value={item[field] || ''} onChange={text => setAttributes({ [key]: value.map((row, n) => n === i ? { ...row, [field]: text } : row) })} />)}<Button variant="tertiary" disabled={i === 0} onClick={() => { const rows = [...value]; [rows[i-1], rows[i]] = [rows[i], rows[i-1]]; setAttributes({ [key]: rows }); }}>Move up</Button><Button isDestructive variant="tertiary" onClick={() => setAttributes({ [key]: value.filter((_, n) => n !== i) })}>Remove</Button></fieldset>)}<Button variant="secondary" onClick={() => setAttributes({ [key]: [...(value || []), { title: '', body: '' }] })}>Add item</Button></div>;
      const Control = key.startsWith('body') ? TextareaControl : TextControl;
      return <Control key={key} label={label(key)} value={value || ''} onChange={text => setAttributes({ [key]: text })} />;
    })}
  </div>;
}
