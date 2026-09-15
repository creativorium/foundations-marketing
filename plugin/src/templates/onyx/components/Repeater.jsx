/**
 * Repeater — the editor control every Onyx block with a list of items uses.
 *
 * This is a deliberate copy of plugin/src/components/Repeater.jsx, not an import of it.
 * A sold template has to be liftable as ONE folder (how-to-work.md §0.1, §2.1b): the
 * packager compiles this design's editor bundle from `plugin/src/templates/onyx/` alone,
 * so a reach back into the marketing site's components would either break the package or
 * quietly drag the marketing site into a client's build.
 *
 * The cost — a fix here does not reach the marketing site's copy, and vice versa — is the
 * same independence trade-off §0.1 records for blocks, accepted for the same reason.
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
  const update = (index, patch) =>
    onChange(items.map((item, i) => (i === index ? { ...item, ...patch } : item)));

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
        <PanelBody key={index} title={label(item, index)} initialOpen={false}>
          {children(item, (patch) => update(index, patch), index)}

          <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
            <Button variant="tertiary" onClick={() => move(index, -1)} disabled={index === 0}>
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
