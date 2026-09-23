import '../../components/category.js';
import metadata from './block.json';
import register from '../../components/register.jsx';
import './editor.scss';

register(metadata, {
  items: [['title', 'Treatment', 'text'], ['body', 'Description', 'textarea'], ['price', 'Price', 'text'], ['duration', 'Duration', 'text']],
});
