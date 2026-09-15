<?php
declare(strict_types=1);
if(!defined('ABSPATH')){exit;}

function fm_delivery_routes(): void
{
    add_rewrite_rule('^templates/([^/]+)/demo(?:/([^/]+))?/?$','index.php?fm_delivery_demo=$matches[1]&fm_delivery_page=$matches[2]','top');
}
add_action('init','fm_delivery_routes',30);
add_filter('query_vars',function(array $vars):array{return array_merge($vars,['fm_delivery_demo','fm_delivery_page']);});
add_filter('post_type_link',function(string $link,WP_Post $post):string{
    if($post->post_type!=='fm_project_page'){return $link;}
    $parent=get_post($post->post_parent);if(!$parent){return $link;}
    $slug=get_post_meta($post->ID,'_fm_content_slug',true)?:$post->post_name;
    $content=(array)get_post_meta($parent->ID,'_fm_content',true);
    $home=$content['manifest']['homepage']??'home';
    if($parent->post_type==='fm_design'){return home_url('/templates/'.$parent->post_name.'/demo/'.($slug===$home?'':$slug.'/'));}
    return add_query_arg(['fm_project_preview'=>$parent->ID,'fm_page'=>$slug],home_url('/'));
},10,2);

add_filter('fm_site_settings_values',function(array $settings):array{
    $id=(int)($GLOBALS['fm_delivery_context']??0);
    if(!$id){$post=get_post();if($post && $post->post_type==='fm_project_page'){$id=$post->post_parent;}}
    if($id){$content=get_post_meta($id,'_fm_content',true);return (array)($content['settings']??[]);}
    return $settings;
});
add_filter('fm_settings_extensions',function(array $fields):array{
    $id=absint($_GET['project']??$_GET['design']??($GLOBALS['fm_delivery_context']??0));
    if(!$id && isset($_GET['post'])){$post=get_post(absint($_GET['post']));$id=$post?->post_parent??0;}
    $p=(array)get_post_meta($id,'_fm_project',true);$designId=$p['design']??$id;
    if(get_post_type($designId)!=='fm_design'){return $fields;}
    $d=fm_delivery_design((int)$designId);$meta=FM_Delivery_Bundle::json($d['plugin'].'/template.json');
    return array_merge($fields,array_filter((array)($meta['settings']??[]),fn($v,$k)=>!str_starts_with($k,'$')&&is_array($v),ARRAY_FILTER_USE_BOTH));
});

function fm_delivery_asset_url(int $id,string $kind,string $file):string
{
    return add_query_arg(['fm_design_asset'=>$id,'kind'=>$kind,'file'=>$file],home_url('/'));
}
add_action('template_redirect',function():void{
    if(!isset($_GET['fm_design_asset'])){return;}
    $id=absint($_GET['fm_design_asset']);
    if(get_post_type($id)!=='fm_design'||(get_post_status($id)!=='publish'&&!current_user_can('manage_options'))){status_header(404);exit;}
    try{$d=fm_delivery_design($id);$kind=$_GET['kind']??'';$file=(string)wp_unslash($_GET['file']??'');
        if(!in_array($kind,['theme','plugin'],true)){throw new RuntimeException('Unknown asset.');}
        $path=FM_Delivery_Bundle::path($d[$kind],$file);$ext=strtolower(pathinfo($path,PATHINFO_EXTENSION));
        $types=['css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml','woff'=>'font/woff','ttf'=>'font/ttf','otf'=>'font/otf','woff2'=>'font/woff2','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','gif'=>'image/gif'];
        if(!isset($types[$ext])||!is_file($path)){throw new RuntimeException('Unknown asset.');}
        header('Content-Type: '.$types[$ext]);header('X-Content-Type-Options: nosniff');
        if($ext==='css'){
            $css=(string)file_get_contents($path);
            $css=preg_replace_callback('~url\([\'\"]?([^\)\'\"]+)[\'\"]?\)~',function($m)use($id,$kind,$file){if(preg_match('~^(https?:|data:|/|#)~',$m[1])){return $m[0];}$relative=(dirname($file)==='.'?'':dirname($file).'/').preg_split('/[?#]/',$m[1])[0];$segments=[];foreach(explode('/',$relative) as $segment){if($segment==='..'){if(!$segments){throw new RuntimeException('Asset outside design.');}array_pop($segments);}elseif($segment!=='.'&&$segment!==''){$segments[]=$segment;}}$relative=implode('/',$segments);return 'url("'.esc_url_raw(fm_delivery_asset_url($id,$kind,$relative)).'")';},$css);
            echo $css;
        }else{readfile($path);}exit;
    }catch(Throwable $e){status_header(404);exit;}
},0);

add_action('template_redirect',function():void{
    $slug=get_query_var('fm_delivery_demo');$project=absint($_GET['fm_project_preview']??0);
    if(!$slug&&!$project){return;}
    $parent=$project?get_post($project):get_page_by_path(sanitize_title($slug),OBJECT,'fm_design');
    if(!$parent||!in_array($parent->post_type,['fm_project','fm_design'],true)||($project&&!current_user_can('manage_options'))||(!$project&&$parent->post_status!=='publish'&&!current_user_can('manage_options'))){status_header(404);exit('Preview not available.');}
    $id=$parent->ID;$content=(array)get_post_meta($id,'_fm_content',true);$p=(array)get_post_meta($id,'_fm_project',true);$designId=(int)($p['design']??$id);
    $page=sanitize_title($project?($_GET['fm_page']??''):get_query_var('fm_delivery_page'))?:($content['manifest']['homepage']??'home');
    if(!isset($content['pages'][$page])){status_header(404);exit('Page not found.');}
    try{$design=fm_delivery_design($designId);}catch(Throwable $e){status_header(404);exit('Design unavailable.');}
    $GLOBALS['fm_delivery_context']=$id;
    $GLOBALS['post']=get_post($content['pages'][$page]);setup_postdata($GLOBALS['post']);
    global $wp_query;$wp_query->is_singular=true;$wp_query->queried_object=$GLOBALS['post'];$wp_query->queried_object_id=$GLOBALS['post']->ID;
    status_header(200);nocache_headers();header('X-Robots-Tag: noindex, nofollow');
    $themeData=FM_Delivery_Bundle::json($design['theme'].'/theme.json');
    add_filter('wp_theme_json_data_theme',fn()=>new WP_Theme_JSON_Data($themeData,'theme'));
    add_filter('wp_theme_json_data_user',fn()=>new WP_Theme_JSON_Data(['version'=>2],'custom'));
    WP_Theme_JSON_Resolver::clean_cached_data();
    add_filter('pre_render_block',function($pre,array $block)use($design){
        if($block['blockName']!=='core/template-part'){return $pre;}
        $slug=sanitize_key($block['attrs']['slug']??'');$file=$design['theme'].'/parts/'.$slug.'.html';
        if(!is_file($file)){return '';}
        $tag=in_array($block['attrs']['tagName']??'',['header','footer','div'],true)?$block['attrs']['tagName']:'div';
        return '<'.$tag.' class="wp-block-template-part">'.do_blocks((string)file_get_contents($file)).'</'.$tag.'>';
    },10,2);
    $body=do_blocks((string)file_get_contents($design['theme'].'/templates/page.html'));
    $css=wp_get_global_stylesheet().wp_style_engine_get_stylesheet_from_context('block-supports');
    ?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html(get_the_title().' — '.($content['settings']['site_name']??$parent->post_title)); ?></title><style><?php echo str_replace('</style','< /style',$css); ?></style><link rel="stylesheet" href="<?php echo esc_url(fm_delivery_asset_url($designId,'theme','style.css')); ?>"><?php if(is_file($design['plugin'].'/build/frontend.css')): ?><link rel="stylesheet" href="<?php echo esc_url(fm_delivery_asset_url($designId,'plugin','build/frontend.css')); ?>"><?php endif; ?><link rel="stylesheet" href="<?php echo esc_url(includes_url('css/dist/block-library/style.min.css')); ?>"></head><body><a class="fm-skip-link" href="#fm-content">Skip to content</a><div class="wp-site-blocks"><?php echo $body; ?></div><?php if(!$project): ?><aside style="padding:16px;text-align:center;background:#fff;color:#111"><a href="<?php echo esc_url(home_url('/templates/')); ?>">All designs</a> · <a href="<?php echo esc_url(add_query_arg('template',$parent->post_name,function_exists('fm_builder_url')&&fm_builder_url()?fm_builder_url():home_url('/build-your-site/'))); ?>">Choose this design</a></aside><?php endif; ?><?php if(is_file($design['plugin'].'/build/frontend.js')): ?><script src="<?php echo esc_url(fm_delivery_asset_url($designId,'plugin','build/frontend.js')); ?>"></script><?php endif; ?></body></html><?php exit;
},1);

add_action('admin_enqueue_scripts',function():void{
    if(($_GET['page']??'')==='fm-delivery'){
        wp_enqueue_media();wp_enqueue_script('fm-delivery-settings',plugins_url('../runtime/assets/settings.js',__FILE__),['media-editor'],'0.1.0',true);
    }
});
add_action('enqueue_block_editor_assets',function():void{
    $post=get_post();if(!$post||$post->post_type!=='fm_project_page'){return;}
    $parent=$post->post_parent;$p=(array)get_post_meta($parent,'_fm_project',true);$id=(int)($p['design']??$parent);$d=fm_delivery_design($id);
    $GLOBALS['fm_delivery_context']=$parent;
    wp_enqueue_script('fm-shell-editor', plugins_url('../runtime/base/assets/editor.js',__FILE__), ['wp-blocks','wp-element','wp-block-editor','wp-server-side-render'], '0.1.0', true);
    if(is_file($d['plugin'].'/build/editor.js')){wp_enqueue_script('fm-design-editor',fm_delivery_asset_url($id,'plugin','build/editor.js'),['wp-blocks','wp-element','wp-block-editor','wp-components','wp-server-side-render','wp-data','wp-i18n'],'0.1.0',true);}
    foreach(['editor','frontend'] as $asset){if(is_file($d['plugin'].'/build/'.$asset.'.css')){wp_enqueue_style('fm-design-'.$asset,fm_delivery_asset_url($id,'plugin','build/'.$asset.'.css'),[],'0.1.0');}}
});
add_filter('block_editor_settings_all',function(array $settings,$context):array {
    $post=$context->post??null;
    if(!$post || $post->post_type!=='fm_project_page'){return $settings;}
    $p=(array)get_post_meta($post->post_parent,'_fm_project',true);$id=(int)($p['design']??$post->post_parent);$d=fm_delivery_design($id);
    $json=new WP_Theme_JSON(FM_Delivery_Bundle::json($d['theme'].'/theme.json'),'theme');
    $settings['styles'][]=['css'=>$json->get_stylesheet(['variables','styles','presets'])];
    $settings['styles'][]=['css'=>'@import url("'.esc_url_raw(fm_delivery_asset_url($id,'theme','style.css')).'");'];
    foreach(['editor','frontend'] as $asset){if(is_file($d['plugin'].'/build/'.$asset.'.css')){$settings['styles'][]=['css'=>'@import url("'.esc_url_raw(fm_delivery_asset_url($id,'plugin','build/'.$asset.'.css')).'");'];}}
    return $settings;
},10,2);
add_filter('rest_request_before_callbacks',function($response,$handler,WP_REST_Request $request){$post=get_post(absint($request->get_param('post_id')));if($post&&$post->post_type==='fm_project_page'&&current_user_can('edit_post',$post->ID)){$GLOBALS['fm_delivery_context']=$post->post_parent;}return $response;},10,3);
