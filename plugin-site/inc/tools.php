<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function (): void {
    add_management_page('Foundations Delivery','Foundations Delivery','manage_options','fm-site-delivery',function (): void {
        echo '<div class="wrap"><h1>Foundations Delivery</h1><p>Import starter content on a fresh site, or export this customer’s edited site. Themes and plugins are installed separately.</p>';
        $card=(array)get_option('fm_keycard',[]);
        echo '<h2>Keycard</h2><p>'.esc_html($card['id']??'No keycard assigned. The website remains fully active.').'</p>';
        echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="fm_site_import">';wp_nonce_field('fm_site_import');
        echo '<label>Content ZIP <input type="file" name="bundle" accept=".zip" required></label>';submit_button('Import starter content');echo '</form>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="fm_site_export">';wp_nonce_field('fm_site_export');
        $content=(array)get_option('fm_delivery_content',[]);
        echo '<h2>Pages to export</h2><p>Include any pages added after installation. The homepage must remain selected.</p>';
        foreach(get_posts(['post_type'=>'page','post_status'=>['publish','draft','private'],'numberposts'=>-1]) as $page){echo '<p><label><input type="checkbox" name="pages[]" value="'.$page->ID.'" '.checked(in_array($page->ID,array_values($content['pages']??[]),true),true,false).'> '.esc_html($page->post_title).' ('.esc_html($page->post_status).')</label></p>';}
        submit_button('Download edited content','secondary');echo '</form></div>';
    });
});

function fm_site_download(string $file, string $name): void
{
    nocache_headers();header('Content-Type: application/zip');header('X-Content-Type-Options: nosniff');header('Content-Disposition: attachment; filename="'.sanitize_file_name($name).'"');header('Content-Length: '.filesize($file));readfile($file);unlink($file);exit;
}
add_action('admin_post_fm_site_import',function ():void {
    if(!current_user_can('manage_options')){wp_die('Not allowed','',['response'=>403]);}check_admin_referer('fm_site_import');
    $root='';
    try {
        if(get_option('fm_delivery_content')){throw new RuntimeException('This site already has imported content. Use a fresh site to avoid overwriting customer edits.');}
        if(empty($_FILES['bundle']['tmp_name'])||!is_uploaded_file($_FILES['bundle']['tmp_name'])){throw new RuntimeException('Choose a content ZIP.');}
        $root=FM_Delivery_Bundle::unzip($_FILES['bundle']['tmp_name']);
        $result=FM_Delivery_Bundle::import($root, 'page', 0, true);
        update_option('fm_site_settings',fm_site_sanitize_settings($result['settings']));
        update_option('fm_delivery_content',$result,false);
        if(!empty($result['settings']['site_name'])){update_option('blogname',$result['settings']['site_name']);}
        update_option('show_on_front','page');update_option('page_on_front',$result['homepage']);
        update_option('permalink_structure','/%postname%/');flush_rewrite_rules();
        if(is_file($root.'/keycard.json')){$card=FM_Delivery_Bundle::json($root.'/keycard.json');update_option('fm_keycard',['id'=>sanitize_text_field($card['id']??''),'release'=>sanitize_text_field($card['release']??''),'installation'=>wp_generate_uuid4(),'domain'=>home_url(),'installed_at'=>gmdate('c')],false);}
        FM_Delivery_Bundle::remove($root);
        wp_safe_redirect(admin_url('admin.php?page=fm-site-settings&saved=1'));exit;
    }catch(Throwable $e){if($root){FM_Delivery_Bundle::remove($root);}wp_die(esc_html($e->getMessage()));}
});
add_action('admin_post_fm_site_export',function ():void {
    if(!current_user_can('manage_options')){wp_die('Not allowed','',['response'=>403]);}check_admin_referer('fm_site_export');
    try{
        $content=(array)get_option('fm_delivery_content',[]);
        if(empty($content['pages'])){throw new RuntimeException('Import a starter bundle before exporting a customer project.');}
        $pages=[];
        foreach(array_unique(array_map('absint',(array)($_POST['pages']??[]))) as $id){
            $page=get_post($id);if(!$page||$page->post_type!=='page'||!current_user_can('edit_post',$id)){throw new RuntimeException('Invalid page selection.');}
            $slug=get_post_meta($id,'_fm_content_slug',true)?:sanitize_title($page->post_name?:$page->post_title);
            if(!$slug||isset($pages[$slug])){throw new RuntimeException('Selected pages need unique slugs.');}$pages[$slug]=$id;
        }
        if(!in_array((int)get_option('page_on_front'),$pages,true)){throw new RuntimeException('Include the homepage in the export.');}
        $content['pages']=$pages;$content['manifest']['homepage']=array_search((int)get_option('page_on_front'),$pages,true);
        $root=FM_Delivery_Bundle::export($content['pages'],fm_site_sanitize_settings((array)get_option('fm_site_settings',[])),$content['manifest']);
        $card=(array)get_option('fm_keycard',[]);if($card){file_put_contents($root.'/keycard.json',wp_json_encode($card));}
        $file=FM_Delivery_Bundle::private_dir().'/content-'.wp_generate_uuid4().'.zip';FM_Delivery_Bundle::zip($root,$file);FM_Delivery_Bundle::remove($root);fm_site_download($file,'customer-content.zip');
    }catch(Throwable $e){wp_die(esc_html($e->getMessage()));}
});
