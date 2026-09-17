import { registerBlockType } from '@wordpress/blocks';
import { createElement } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import metadata from './block.json';
registerBlockType(metadata.name,{...metadata,edit:({attributes})=>createElement(ServerSideRender,{block:metadata.name,attributes}),save:()=>null});
