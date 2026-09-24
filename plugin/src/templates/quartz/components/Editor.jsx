import { useBlockProps, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { TextControl, TextareaControl, Button } from '@wordpress/components';

/*
 * One generic form for every Quartz block, driven by block.json attributes.
 *
 * Repeaters need to know their row shape, which block.json cannot carry, so each block's
 * index.js passes `fields`: { attributeName: [['key', 'Label', 'text'|'textarea'|'image'], …] }.
 * Image fields store attachment ids and are named …Id so export/import can remap them
 * (team-template-workflow.md, "stable import references").
 */
const LONG = ['heading', 'body', 'intro', 'quote', 'boxText'];
const label = key => key.replace(/([A-Z])/g, ' $1').replace(/^./, char => char.toUpperCase());

function ImageField({ title, value, onChange }) {
  return <div className="quartz-editor__media">
    <MediaUploadCheck>
      <MediaUpload allowedTypes={['image']} value={value} onSelect={media => onChange(media.id)} render={({ open }) => <>
        <Button variant="secondary" onClick={open}>{title}: {value ? 'Replace image' : 'Choose image'}</Button>
        {!!value && <Button variant="tertiary" onClick={() => onChange(0)}>Remove</Button>}
      </>} />
    </MediaUploadCheck>
  </div>;
}

function Field({ kind, title, value, onChange }) {
  if (kind === 'image') return <ImageField title={title} value={value || 0} onChange={onChange} />;
  const Control = kind === 'textarea' ? TextareaControl : TextControl;
  return <Control label={title} value={value || ''} onChange={onChange} />;
}

export default function Editor({ attributes, setAttributes, metadata, fields }) {
  const entries = Object.entries(metadata.attributes || {}).filter(([key]) => key !== 'anchor');
  return <div {...useBlockProps({ className: 'quartz-editor' })}>
    <h3>{metadata.title}</h3>
    {!entries.length && <p>Edit the business details and navigation in Site Settings.</p>}
    {entries.map(([key, schema]) => {
      const value = attributes[key];
      const set = next => setAttributes({ [key]: next });
      if (schema.type === 'array') {
        const rows = value || [];
        const shape = fields[key] || [['title', 'Title', 'text'], ['body', 'Body', 'textarea']];
        const blank = Object.fromEntries(shape.map(([field, , kind]) => [field, kind === 'image' ? 0 : '']));
        const update = (index, field, next) => set(rows.map((row, rowIndex) => rowIndex === index ? { ...row, [field]: next } : row));
        return <div key={key}>
          <h4>{label(key)}</h4>
          {rows.map((row, index) => <fieldset key={index}>
            <legend>Item {index + 1}</legend>
            {shape.map(([field, title, kind]) => <Field key={field} kind={kind} title={title} value={row[field]} onChange={next => update(index, field, next)} />)}
            <Button variant="tertiary" disabled={index === 0} onClick={() => { const next = [...rows]; [next[index - 1], next[index]] = [next[index], next[index - 1]]; set(next); }}>Move up</Button>
            <Button isDestructive variant="tertiary" onClick={() => set(rows.filter((_, rowIndex) => rowIndex !== index))}>Remove</Button>
          </fieldset>)}
          <Button variant="secondary" onClick={() => set([...rows, blank])}>Add item</Button>
        </div>;
      }
      if (schema.type === 'integer') return <Field key={key} kind="image" title={label(key)} value={value} onChange={set} />;
      return <Field key={key} kind={LONG.includes(key) ? 'textarea' : 'text'} title={label(key)} value={value} onChange={set} />;
    })}
  </div>;
}
