import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const COLORS = [
  ['paper', '--fm-bg', __('Paper colour', 'foundations')],
  ['ink', '--fm-ink', __('Ink colour', 'foundations')],
  ['accent', '--fm-accent', __('Accent colour', 'foundations')],
  ['panel', '--fm-bg-2', __('Recessed colour', 'foundations')],
];

export default function Edit({ attributes, setAttributes }) {
  const style = Object.fromEntries(COLORS
    .filter(([key]) => /^#[\da-f]{6}$/i.test(attributes[key]))
    .map(([key, token]) => [token, attributes[key]]));
  return <>
    <InspectorControls>
      <PanelBody title={__('Canvas palette', 'foundations')}>
        {COLORS.map(([key, token, label]) => <TextControl key={key} label={label}
          help={__('Six-digit hex colour; leave empty to inherit the site palette. Check contrast after changes.', 'foundations')}
          value={attributes[key]} onChange={(value) => setAttributes({ [key]: value })} />)}
      </PanelBody>
    </InspectorControls>
    <div {...useBlockProps({ className: 'fm-editorial-frame', style })}>
      <InnerBlocks renderAppender={InnerBlocks.ButtonBlockAppender} />
    </div>
  </>;
}

// Dynamic containers retain nested block comments; PHP renders the outer wrapper.
export function Save() { return <InnerBlocks.Content />; }
