/**
 * Repeater — shared editor control for blocks with a list of items.
 *
 * Steps, benefits, FAQs and quotes all need "a list of small records, reorderable,
 * add/remove". This is that control once, rather than four near-identical copies.
 *
 * Usage:
 *
 *   <Repeater
 *     items={items}
 *     onChange={(next) => setAttributes({ items: next })}
 *     blank={{ title: '', body: '' }}
 *     label={(item, i) => item.title || `Item ${i + 1}`}
 *     addLabel={__('Add step', 'foundations')}
 *   >
 *     {(item, update) => (
 *       <TextControl value={item.title} onChange={(v) => update({ title: v })} />
 *     )}
 *   </Repeater>
 */
import { Button, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Repeater({
  items = [],
  onChange,
  blank = {},
  label = (item, i) => `Item ${i + 1}`,
  addLabel = __('Add item', 'foundations'),
  children,
}) {
  const update = (index, patch) => {
    const next = items.map((item, i) => (i === index ? { ...item, ...patch } : item));
    onChange(next);
  };

  const remove = (index) => onChange(items.filter((_, i) => i !== index));

  const move = (index, delta) => {
    const target = index + delta;

    if (target < 0 || target >= items.length) {
      return;
    }

    const next = [...items];
    [next[index], next[target]] = [next[target], next[index]];
    onChange(next);
  };

  return (
    <>
      {items.map((item, index) => (
        <PanelBody
          key={index}
          title={label(item, index)}
          initialOpen={false}
        >
          {children(item, (patch) => update(index, patch), index)}

          <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
            <Button
              variant="tertiary"
              onClick={() => move(index, -1)}
              disabled={index === 0}
            >
              {__('Move up', 'foundations')}
            </Button>
            <Button
              variant="tertiary"
              onClick={() => move(index, 1)}
              disabled={index === items.length - 1}
            >
              {__('Move down', 'foundations')}
            </Button>
            <Button variant="link" isDestructive onClick={() => remove(index)}>
              {__('Remove', 'foundations')}
            </Button>
          </div>
        </PanelBody>
      ))}

      <div style={{ padding: '16px' }}>
        <Button variant="primary" onClick={() => onChange([...items, { ...blank }])}>
          {addLabel}
        </Button>
      </div>
    </>
  );
}
