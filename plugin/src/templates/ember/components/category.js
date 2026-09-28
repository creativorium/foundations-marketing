import { getCategories, setCategories } from '@wordpress/blocks';

const slug = 'foundations-ember';
if (!getCategories().some((category) => category.slug === slug)) {
  setCategories([...getCategories(), { slug, title: 'Ember Massage Therapy', icon: 'heart' }]);
}
