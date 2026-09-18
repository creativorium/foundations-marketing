import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';

export default function Edit() {
  return <div className="fm-pk-shell-editor">
    <div>Pulse Kinetic · Footer (edit content in Site Settings)</div>
    <ServerSideRender block={metadata.name} />
  </div>;
}
