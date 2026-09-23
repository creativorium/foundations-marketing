import '../../components/category.js';
import metadata from './block.json';
import register from '../../components/register.jsx';
import './editor.scss';

register(metadata, {
  sections: [['title', 'Section heading', 'text'], ['body', 'Section text', 'textarea']],
});
