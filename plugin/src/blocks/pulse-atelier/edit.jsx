import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import Settings from './Settings.jsx';
import defaults from './defaults.json';
import sectionDefaults from '../pulse-atelier-section/defaults.json';

export default function Edit({ attributes, setAttributes }) {
  const settings = attributes.settings ?? {};
  return <>
    <InspectorControls>{Object.entries(defaults).map(([part, preset]) => <PanelBody key={part} title={part[0].toUpperCase() + part.slice(1)} initialOpen={false}>
      <Settings defaults={preset} value={settings[part]} onChange={next => setAttributes({ settings: { ...settings, [part]: next } })} />
    </PanelBody>)}</InspectorControls>
    <div {...useBlockProps({ className: 'fm-pa-editor-shell' })}>
      <div className="fm-pa-editor-label">Pulse Atelier · Header, footer and navigation views are editable in block settings.</div>
      <InnerBlocks allowedBlocks={['foundations/pulse-atelier-section']} template={Object.keys(sectionDefaults).map(variant => ['foundations/pulse-atelier-section', { variant }])} />
    </div>
  </>;
}
export function Save() { return <InnerBlocks.Content />; }
