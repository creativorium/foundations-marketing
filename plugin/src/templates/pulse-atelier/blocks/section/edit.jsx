import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import Settings from '../../components/Settings.jsx';
import defaults from './defaults.json';

export default function Edit({ attributes, setAttributes }) {
  const variant = defaults[attributes.variant] ? attributes.variant : 'hero';
  return <>
    <InspectorControls><PanelBody title="Section layout">
      <SelectControl label="Reference section" value={variant} options={Object.keys(defaults).map(value => ({ value, label: value[0].toUpperCase() + value.slice(1) }))} onChange={next => setAttributes({ variant: next, settings: {} })} />
    </PanelBody><Settings defaults={defaults[variant]} value={attributes.settings} onChange={settings => setAttributes({ settings })} /></InspectorControls>
    <div {...useBlockProps()}>
      <div className="fm-pa-editor-label">Pulse Atelier · {variant}</div>
      <div className="fm-pulse-atelier fm-pa-editor-preview"><ServerSideRender block="foundations/pulse-atelier-section" attributes={attributes} /></div>
    </div>
  </>;
}



