import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import Settings from '../../components/Settings.jsx';
import defaults from './defaults.json';

const names = {
  hero: 'Hero and marquee',
  metrics: 'Studio metrics',
  classes: 'Classes',
  'free-week': 'Free week',
  coach: 'Coach',
  pricing: 'Pricing',
  timetable: 'Timetable',
  testimonials: 'Testimonials',
  faq: 'FAQ',
  booking: 'Booking',
  contact: 'Contact page',
  privacy: 'Privacy page',
};

export default function Edit({ attributes, setAttributes }) {
  const variant = defaults[attributes.variant] ? attributes.variant : 'hero';
  return <>
    <InspectorControls>
      <PanelBody title="Section layout">
        <SelectControl
          label="Reference section"
          value={variant}
          options={Object.keys(defaults).map((value) => ({ value, label: names[value] }))}
          onChange={(next) => setAttributes({ variant: next, settings: {} })}
        />
      </PanelBody>
      <Settings defaults={defaults[variant]} value={attributes.settings} onChange={(settings) => setAttributes({ settings })} />
    </InspectorControls>
    <div {...useBlockProps()}>
      <div className="fm-pk-editor-label">Pulse Kinetic · {names[variant]}</div>
      <ServerSideRender block="foundations/pulse-kinetic-section" attributes={attributes} />
    </div>
  </>;
}
