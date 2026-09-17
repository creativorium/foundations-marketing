<?php
// Local acceptance harness. Credentials are written only to gitignored audit files.
if (PHP_SAPI !== 'cli' || count($argv)!==3) { exit("CLI: wp-load.php and audit output path\n"); }
define('WP_HTTP_BLOCK_EXTERNAL',true);define('DISABLE_WP_CRON',true);
require $argv[1];
if (!str_ends_with(wp_parse_url(home_url(),PHP_URL_HOST),'.local')) { throw new RuntimeException('Local test sites only'); }
require_once ABSPATH.'wp-admin/includes/plugin.php';
if (str_contains(home_url(),'foundations-fixture.local')) {
    $before=['stylesheet'=>get_option('stylesheet'),'front'=>get_option('page_on_front'),'content'=>get_option('fm_delivery_content')];
    file_put_contents(dirname($argv[2]).'/fixture-before-delivery.json',wp_json_encode($before));
    if (!$before['content']) {
        foreach (['home'=>5,'about'=>6,'contact'=>7] as $slug=>$expected) {
            $old=get_page_by_path($slug);
            if ($old && $old->ID!==$expected) { throw new RuntimeException('Unexpected existing page. Stop before moving content.'); }
            if ($old) { wp_update_post(['ID'=>$old->ID,'post_status'=>'draft','post_name'=>'previous-fixture-'.$slug]); }
        }
    }
    switch_theme('foundations-base-three-page');
    $error=activate_plugin('foundations-site/foundations-site.php');
    if(is_wp_error($error)){throw new RuntimeException($error->get_error_message());}
    fm_site_roles();
}
$admin=get_users(['role'=>'administrator','number'=>1])[0];
$cookies=[];$expires=time()+3600;
$logged=wp_generate_auth_cookie($admin->ID,$expires,'logged_in');
$parsed=wp_parse_auth_cookie($logged,'logged_in');
foreach([LOGGED_IN_COOKIE=>$logged,AUTH_COOKIE=>wp_generate_auth_cookie($admin->ID,$expires,'auth',$parsed['token'])] as $name=>$value){$cookies[]=['name'=>$name,'value'=>$value,'domain'=>wp_parse_url(home_url(),PHP_URL_HOST),'path'=>'/','httpOnly'=>true,'secure'=>false,'sameSite'=>'Lax'];}
file_put_contents($argv[2],wp_json_encode($cookies));
file_put_contents($argv[2].'.session',wp_json_encode(['user'=>$admin->ID,'token'=>$parsed['token']]));
echo 'Local browser session prepared for '.home_url()."\n";
