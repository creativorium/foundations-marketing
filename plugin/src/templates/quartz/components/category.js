import { getCategories, setCategories } from '@wordpress/blocks';

const slug = 'foundations-quartz';
if (!getCategories().some(category => category.slug === slug)) {
  setCategories([...getCategories(), { slug, title: 'Quartz Facialist', icon: 'star-empty' }]);
}
