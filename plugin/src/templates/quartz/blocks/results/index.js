import '../../components/category.js';
import metadata from './block.json';
import register from '../../components/register.jsx';
import './editor.scss';

register(metadata, {
  pairs: [
    ['divider', 'Divider label (shown above every pair after the first)', 'text'],
    ['beforeStage', 'Before — stage', 'text'],
    ['beforeTitle', 'Before — concern', 'text'],
    ['beforeImageId', 'Before photo', 'image'],
    ['afterStage', 'After — stage', 'text'],
    ['afterTitle', 'After — result', 'text'],
    ['afterImageId', 'After photo', 'image'],
  ],
});
