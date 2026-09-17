import { getCategories, setCategories } from '@wordpress/blocks';

// Customer packages do not carry the marketing plugin's category registration.
const slug = 'foundations-meridian-classic';
if (!getCategories().some(category => category.slug === slug)) {
  setCategories([...getCategories(), { slug, title: 'Meridian Classic', icon: 'admin-site-alt3' }]);
}
