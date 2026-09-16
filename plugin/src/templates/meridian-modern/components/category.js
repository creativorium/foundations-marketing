import { getCategories, setCategories } from '@wordpress/blocks';

// Customer packages do not carry the marketing plugin's category registration.
const slug = 'foundations-meridian-modern';
if (!getCategories().some(category => category.slug === slug)) {
  setCategories([...getCategories(), { slug, title: 'Meridian Modern', icon: 'admin-site-alt3' }]);
}
