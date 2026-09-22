import { getCategories, setCategories } from '@wordpress/blocks';

const slug = 'foundations-bloom';
if (!getCategories().some(category => category.slug === slug)) {
  setCategories([...getCategories(), { slug, title: 'Bloom Birth Support', icon: 'heart' }]);
}
