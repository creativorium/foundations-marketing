<?php
/** Local runtime URL regression. Reuses existing content; removes its temporary page. */
if (PHP_SAPI !== 'cli' || count($argv) !== 2) { exit("Usage: php test-delivery-urls-wordpress.php /path/to/wp-load.php\n"); }
define('WP_HTTP_BLOCK_EXTERNAL',true);define('DISABLE_WP_CRON',true);require $argv[1];
if (!str_ends_with((string) wp_parse_url(home_url(),PHP_URL_HOST),'.local')) { throw new RuntimeException('Local only'); }
$content=get_option('fm_delivery_content');
if (empty($content['pages']['home'])) { throw new RuntimeException('Existing fixture content required'); }
$pages=$content['pages'];$home=$pages['home'];$contact=$pages['contact'];
$settings=['old_home'=>add_query_arg('page_id',$home,home_url('/')).'#top',
    'old_contact'=>add_query_arg('page_id',$contact,home_url('/')).'#form',
    'old_home_slug'=>home_url('/'.get_page_uri($home).'/').'#top',
    'current_contact'=>get_permalink($contact),
    'unrelated'=>home_url('/unselected-page/'),'different_id'=>add_query_arg('page_id',$home.'999',home_url('/'))];
$root=FM_Delivery_Bundle::export($pages,$settings,$content['manifest']);
try {
    $actual=FM_Delivery_Bundle::json($root.'/settings.json');
    foreach(['old_home'=>'{{page:home|url}}#top','old_contact'=>'{{page:contact|url}}#form','old_home_slug'=>'{{page:home|url}}#top','current_contact'=>'{{page:contact|url}}','unrelated'=>$settings['unrelated'],'different_id'=>$settings['different_id']] as $key=>$expected){
        if(($actual[$key]??null)!==$expected){throw new RuntimeException('URL regression: '.$key.' = '.($actual[$key]??'missing'));}
    }
} finally { FM_Delivery_Bundle::remove($root); }
$root=FM_Delivery_Bundle::private_dir().'/url-test-'.wp_generate_uuid4();wp_mkdir_p($root.'/pages');wp_mkdir_p($root.'/media');
$slug='fm-url-test-'.strtolower(wp_generate_password(8,false,false));$saved=[];$created=[];
foreach(['permalink_structure','show_on_front','page_on_front'] as $key){$saved[$key]=get_option($key);}
try {
    file_put_contents($root.'/manifest.json',wp_json_encode(['homepage'=>$slug,'pages'=>[['slug'=>$slug,'title'=>'Temporary URL regression','file'=>'pages/home.blocks.txt']]]));
    file_put_contents($root.'/pages/home.blocks.txt','<!-- wp:paragraph --><p><a href="{{page:'.$slug.'|url}}#top">Home</a></p><!-- /wp:paragraph -->');
    file_put_contents($root.'/settings.json',wp_json_encode(['home_url'=>'{{page:'.$slug.'|url}}#top']));
    file_put_contents($root.'/navigation.json','{}');file_put_contents($root.'/media.json','{"files":[]}');
    update_option('permalink_structure','');update_option('show_on_front','posts');
    $result=FM_Delivery_Bundle::import($root,'page',0,true);$created=array_values($result['pages']);
    if($result['settings']['home_url']!==home_url('/').'#top'){throw new RuntimeException('Homepage resolved before site options were configured');}
    if(str_contains(get_post($created[0])->post_content,'?page_id=')){throw new RuntimeException('Query URL survived new import');}
} finally {
    foreach($saved as $key=>$value){update_option($key,$value);}
    foreach($created as $id){wp_delete_post($id,true);}
    FM_Delivery_Bundle::remove($root);
}
echo "URL export aliases, anchor preservation, URL boundaries and fresh-site homepage resolution passed. No test pages retained.\n";
