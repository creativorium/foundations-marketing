import {registerBlockType} from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import {createElement} from '@wordpress/element';
import metadata from './block.json';
registerBlockType(metadata.name,{...metadata,edit:()=>createElement(ServerSideRender,{block:metadata.name}),save:()=>null});
