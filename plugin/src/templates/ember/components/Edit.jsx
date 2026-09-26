import { useBlockProps } from '@wordpress/block-editor';
import { Button, TextControl, TextareaControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const label = (key) => key.replace(/([A-Z])/g, ' $1').replace(/^./, (char) => char.toUpperCase());
const isLong = (key) => /(body|description|quote|paragraph|caption)/i.test(key);

export default function createEdit(metadata) {
  return function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({ className: 'ember-editor' });
    const schemas = Object.entries(metadata.attributes || {});

    return (
      <div {...blockProps}>
        <h3>{metadata.title}</h3>
        {!schemas.length && <p>Edit this block through Site Settings.</p>}
        {schemas.map(([key, schema]) => {
          const value = attributes[key] ?? schema.default ?? '';
          if (schema.type !== 'array') {
            const Control = isLong(key) ? TextareaControl : TextControl;
            return <Control key={key} label={label(key)} value={value} onChange={(next) => setAttributes({ [key]: next })} />;
          }

          const rows = Array.isArray(value) ? value : [];
          const prototype = rows[0] || (schema.default || [])[0] || { title: '', body: '' };
          return (
            <div className="ember-editor__repeater" key={key}>
              <h4>{label(key)}</h4>
              {rows.map((row, index) => (
                <fieldset key={index}>
                  <legend>{label(key)} {index + 1}</legend>
                  {Object.keys(prototype).map((field) => {
                    const Control = isLong(field) ? TextareaControl : TextControl;
                    return <Control key={field} label={label(field)} value={row[field] || ''} onChange={(next) => setAttributes({ [key]: rows.map((item, rowIndex) => rowIndex === index ? { ...item, [field]: next } : item) })} />;
                  })}
                  <Button variant="tertiary" disabled={index === 0} onClick={() => { const next = [...rows]; [next[index - 1], next[index]] = [next[index], next[index - 1]]; setAttributes({ [key]: next }); }}>Move up</Button>
                  <Button isDestructive variant="tertiary" onClick={() => setAttributes({ [key]: rows.filter((_, rowIndex) => rowIndex !== index) })}>Remove</Button>
                </fieldset>
              ))}
              <Button variant="secondary" onClick={() => setAttributes({ [key]: [...rows, Object.fromEntries(Object.keys(prototype).map((field) => [field, '']))] })}>Add item</Button>
            </div>
          );
        })}
        <ServerSideRender block={metadata.name} attributes={attributes} />
      </div>
    );
  };
}
