import { Button, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Repeater({ items = [], onChange, blank = {}, label = (_, index) => `Item ${index + 1}`, children }) {
  const update = (index, patch) => onChange(items.map((item, i) => i === index ? { ...item, ...patch } : item));
  const remove = (index) => onChange(items.filter((_, i) => i !== index));
  const move = (index, delta) => {
    const target = index + delta;
    if (target < 0 || target >= items.length) return;
    const next = [...items];
    [next[index], next[target]] = [next[target], next[index]];
    onChange(next);
  };
  return <>
    {items.map((item, index) => <PanelBody key={index} title={label(item, index)} initialOpen={false}>
      {children(item, (patch) => update(index, patch), index)}
      <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
        <Button variant="tertiary" onClick={() => move(index, -1)} disabled={index === 0}>{__('Move up', 'foundations')}</Button>
        <Button variant="tertiary" onClick={() => move(index, 1)} disabled={index === items.length - 1}>{__('Move down', 'foundations')}</Button>
        <Button variant="link" isDestructive onClick={() => remove(index)}>{__('Remove', 'foundations')}</Button>
      </div>
    </PanelBody>)}
    <div style={{ padding: 16 }}><Button variant="primary" onClick={() => onChange([...items, { ...blank }])}>{__('Add item', 'foundations')}</Button></div>
  </>;
}
