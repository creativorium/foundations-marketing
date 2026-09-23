import '../../components/category.js';
import metadata from './block.json';
import register from '../../components/register.jsx';
import './editor.scss';

register(metadata, {
  stats: [['number', 'Number', 'text'], ['label', 'Label', 'text']],
});
