<?php
/** Run against a disposable/local WordPress, never a production database.
 * php -d mysqli.default_port=PORT scripts/test-delivery-wordpress.php /path/to/wp-load.php /path/to/release.zip
 */
if (PHP_SAPI !== 'cli' || count($argv) !== 3) { exit("CLI: provide wp-load.php and release ZIP\n"); }
define('WP_HTTP_BLOCK_EXTERNAL', true);
define('DISABLE_WP_CRON', true);
require $argv[1];
if(!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1'],true)&&!str_ends_with(wp_parse_url(home_url(),PHP_URL_HOST),'.local')){throw new RuntimeException('Run delivery acceptance on a local disposable site only.');}
require_once ABSPATH.'wp-admin/includes/plugin.php';
$error = activate_plugin('foundations-delivery/foundations-delivery.php');
if (is_wp_error($error)) { throw new RuntimeException($error->get_error_message()); }
fm_delivery_types();
foreach (['site-header','site-footer'] as $name) {
    if (!WP_Block_Type_Registry::get_instance()->is_registered('foundations/'.$name)) {
        register_block_type(FM_DELIVERY_DIR.'runtime/base/blocks/'.$name);
    }
}
$admin = get_users(['role'=>'administrator','number'=>1])[0]; wp_set_current_user($admin->ID);
foreach(['../escape.txt','.. /escape.txt','C:/escape.txt','/escape.txt','nested/../../escape.txt','nested\\escape.txt'] as $unsafe){
    try { FM_Delivery_Bundle::path('/private',$unsafe); throw new LogicException('Unsafe path accepted: '.$unsafe); }
    catch (RuntimeException $e) {}
}
foreach(['{{page:missing|url}}','{{page:home|url'] as $invalid){
    try { FM_Delivery_Bundle::resolve($invalid,[],[]); throw new LogicException('Invalid token accepted'); }
    catch (RuntimeException $e) {}
}
if(FM_Delivery_Bundle::resolve('{{page:home|id}}',['home'=>123],[])!==123){throw new LogicException('ID token is not an integer');}
$design = get_page_by_path('base-three-page', OBJECT, 'fm_design');
if ($design) {
    $archive=new ZipArchive();$archive->open($argv[2]);$incoming=json_decode($archive->getFromName('release.json'),true);$archive->close();
    $stored=get_post_meta($design->ID,'_fm_design',true);
    if(($stored['release']['id']??'')!==($incoming['id']??'')) {
        wp_update_post(['ID'=>$design->ID,'post_name'=>'base-three-page-previous-'.$design->ID]);
        $design=null;
    }
}
if ($design && empty(get_post_meta($design->ID, '_fm_design', true)['root'])) { throw new RuntimeException('Existing fixture has incomplete metadata; inspect before retrying.'); }
$designId = $design ? $design->ID : fm_delivery_add_design($argv[2]);
$id = fm_delivery_create_project($designId,'Delivery acceptance '.gmdate('Y-m-d H:i:s'),'Local test only','Round-trip fixture');
$content = get_post_meta($id,'_fm_content',true);
$master = get_post_meta($designId,'_fm_content',true);
if (array_intersect($content['pages'],$master['pages']) || array_intersect($content['media'],$master['media'])) { throw new RuntimeException('Project isolation failed'); }
$home = $content['pages']['home'];
update_post_meta(array_values($content['media'])[0],'_wp_attachment_image_alt','Customer image description');
wp_update_post(['ID'=>$home,'post_title'=>'Customer edited home']);
$link='<!-- wp:paragraph --><p><a href="'.esc_url(get_permalink($content['pages']['contact'])).'">Contact project</a></p><!-- /wp:paragraph -->';
wp_update_post(wp_slash(['ID'=>$home,'post_content'=>get_post($home)->post_content."\n".$link]));
$release = fm_delivery_release($id);
$root = FM_Delivery_Bundle::unzip($release['path'],true);
$portable = FM_Delivery_Bundle::unzip($root.'/content.zip');
foreach(glob($portable.'/pages/*.blocks.txt') as $file){if(str_contains(file_get_contents($file),'fm_project_preview')){throw new RuntimeException('Source project link survived export');}}
$roundTrip = FM_Delivery_Bundle::import($portable,'fm_project_page',$id);
if (get_the_title($roundTrip['pages']['home']) !== 'Customer edited home') { throw new RuntimeException('Edits lost'); }
foreach ($roundTrip['pages'] as $page) {
    if (str_contains(get_post($page)->post_content,'{{')) { throw new RuntimeException('Unresolved token'); }
}
if ($roundTrip['settings']['nav_primary'][0]['page'] !== $roundTrip['pages']['home']) { throw new RuntimeException('Navigation not remapped'); }
if(get_post_meta(array_values($roundTrip['media'])[0],'_wp_attachment_image_alt',true)!=='Customer image description'){throw new RuntimeException('Image alternative text lost');}
$card = FM_Delivery_Bundle::json($portable.'/keycard.json');
if ($card['id'] !== get_post_meta($id,'_fm_project',true)['keycard']) { throw new RuntimeException('Keycard lost'); }
FM_Delivery_Bundle::remove($portable); FM_Delivery_Bundle::remove($root);
foreach ($roundTrip['pages'] as $page) { wp_delete_post($page,true); }
foreach ($roundTrip['media'] as $media) { wp_delete_attachment($media,true); }
fm_delivery_routes(); flush_rewrite_rules();
$report=['passed'=>true,'design'=>$designId,'project'=>$id,'release'=>$release['id'],'pages'=>$content['pages'],'archive'=>$release['path']];
if(is_dir(__DIR__.'/../doc/fixture-audit')){file_put_contents(__DIR__.'/../doc/fixture-audit/delivery-roundtrip.json',wp_json_encode($report,JSON_PRETTY_PRINT));}
echo wp_json_encode($report,JSON_PRETTY_PRINT)."\n";
