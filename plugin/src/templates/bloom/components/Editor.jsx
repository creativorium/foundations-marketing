import { useBlockProps, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { TextControl, TextareaControl, Button } from '@wordpress/components';

export default function Editor({ attributes, setAttributes, metadata }) {
  const label = key => key.replace(/([A-Z])/g, ' $1').replace(/^./, char => char.toUpperCase());
  return <div {...useBlockProps({ className: 'bloom-editor' })}>
    <h3>{metadata.title}</h3>
    {!Object.keys(metadata.attributes).length && <p>Edit shared business details and navigation in Site Settings.</p>}
    {Object.entries(metadata.attributes).map(([key, schema]) => {
      const value = attributes[key];
      if (schema.type === 'integer') return <div className="bloom-editor__media" key={key}><MediaUploadCheck><MediaUpload allowedTypes={['image']} value={value} onSelect={media => setAttributes({ [key]: media.id })} render={({ open }) => <><Button variant="secondary" onClick={open}>{label(key)}: {value ? 'Replace image' : 'Choose image'}</Button>{!!value && <Button variant="tertiary" onClick={() => setAttributes({ [key]: 0 })}>Remove</Button>}</>} /></MediaUploadCheck></div>;
      if (schema.type === 'array') return <div key={key}><h4>{label(key)}</h4>{(value || []).map((item, index) => <fieldset key={index}><legend>Item {index + 1}</legend><TextControl label="Title" value={item.title || ''} onChange={title => setAttributes({ [key]: value.map((row, rowIndex) => rowIndex === index ? { ...row, title } : row) })} /><TextareaControl label="Body" value={item.body || ''} onChange={body => setAttributes({ [key]: value.map((row, rowIndex) => rowIndex === index ? { ...row, body } : row) })} /><Button variant="tertiary" disabled={index === 0} onClick={() => { const rows = [...value]; [rows[index - 1], rows[index]] = [rows[index], rows[index - 1]]; setAttributes({ [key]: rows }); }}>Move up</Button><Button isDestructive variant="tertiary" onClick={() => setAttributes({ [key]: value.filter((_, rowIndex) => rowIndex !== index) })}>Remove</Button></fieldset>)}<Button variant="secondary" onClick={() => setAttributes({ [key]: [...(value || []), { title: '', body: '' }] })}>Add item</Button></div>;
      const Control = ['body', 'intro', 'quote'].includes(key) ? TextareaControl : TextControl;
      return <Control key={key} label={label(key)} value={value || ''} onChange={text => setAttributes({ [key]: text })} />;
    })}
  </div>;
}
