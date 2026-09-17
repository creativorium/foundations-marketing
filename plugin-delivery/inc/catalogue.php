<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

add_filter('fm_catalogue_templates', function (array $rows, int $limit): array {
    // Only a compiled, explicitly published master can be selected for delivery.
    // Legacy catalogue records remain in WordPress but are not sellable releases.
    $rows = [];
    foreach (get_posts(['post_type'=>'fm_design','post_status'=>'publish','numberposts'=>-1]) as $post) {
        $design = fm_delivery_design($post->ID);
        if (!empty($design['release']['fixture'])) { continue; }
        $meta = FM_Delivery_Bundle::json($design['plugin'].'/template.json');
        $content = (array) get_post_meta($post->ID, '_fm_content', true);
        $rows = array_values(array_filter($rows, fn($row)=>$row['slug']!==$post->post_name));
        $rows[] = ['id'=>$post->ID,'slug'=>$post->post_name,'name'=>$post->post_title,
            'niche'=>$meta['niche']??'', 'description'=>$meta['description']??'',
            'category'=>$meta['category']??'', 'sections'=>$meta['sections']??[],
            'packages'=>$meta['packages']??[], 'thumb_id'=>(int)get_post_meta($post->ID,'_fm_preview_image',true),
            'url'=>home_url('/templates/'.$post->post_name.'/demo/')];
    }
    return $limit < 0 ? $rows : array_slice($rows, 0, $limit);
}, 10, 2);

add_filter('fmcd/discovery_templates', function (): array {
    $options=[];
    foreach(function_exists('fm_get_templates')?fm_get_templates(-1):[] as $row){
        $design=fm_delivery_design($row['id']);$meta=FM_Delivery_Bundle::json($design['plugin'].'/template.json');
        $options[]=['value'=>$row['slug'],'label'=>$row['name'],'url'=>$row['url'],
            'colors'=>max(0,(int)($meta['discovery']['colors']??0)),
            'image_count'=>max(0,(int)($meta['discovery']['image_count']??0)), 'image_guide'=>''];
    }
    return $options?:[['value'=>'choose-with-team','label'=>'Choose with our team','url'=>'','colors'=>0,'image_count'=>0,'image_guide'=>'']];
});

// An order records requested work, even before a payment integration exists.
// Retrying the checkout hook cannot create a second project for the same line.
function fm_delivery_order_projects($order): void {
    if (is_numeric($order)) { $order = wc_get_order($order); }
    if (!$order) { return; }
    foreach ($order->get_items() as $item) {
        $slug = $item->get_meta('_fm_template');
        if (!$slug || $item->get_meta('_fm_project_id')) { continue; }
        $design = get_page_by_path($slug, OBJECT, 'fm_design');
        if (!$design || $design->post_status !== 'publish') { continue; }
        $lock = 'fm_delivery_order_item_'.$item->get_id();
        if (!add_option($lock, time(), '', false)) { continue; }
        try {
            $id = fm_delivery_create_project($design->ID, 'Order '.$order->get_order_number().' — '.$design->post_title,
                trim($order->get_formatted_billing_full_name()), 'Review the order and requested extras before delivery.', $order->get_id());
            $item->update_meta_data('_fm_project_id', $id);
            $item->save();
        } catch (Throwable $e) {
            $order->add_order_note('Delivery project could not be created: '.$e->getMessage());
        } finally { delete_option($lock); }
    }
}
add_action('woocommerce_checkout_order_processed', 'fm_delivery_order_projects');
add_action('woocommerce_store_api_checkout_order_processed', 'fm_delivery_order_projects');
