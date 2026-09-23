import '../../components/category.js';
import metadata from './block.json';
import register from '../../components/register.jsx';
import './editor.scss';

register(metadata, {
  items: [['quote', 'Review', 'textarea'], ['name', 'Client name', 'text'], ['detail', 'Treatment', 'text']],
});
