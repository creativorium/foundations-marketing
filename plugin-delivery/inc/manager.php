<?php
declare(strict_types=1);
if(!defined('ABSPATH')){exit;}

function fm_delivery_types(): void
{
    foreach(['fm_design'=>'Master Designs','fm_project'=>'Customer Projects'] as $type=>$label){register_post_type($type,['label'=>$label,'public'=>false,'show_ui'=>false,'supports'=>['title'],'capability_type'=>'post','map_meta_cap'=>true]);}
    register_post_type('fm_project_page',['label'=>'Project Pages','public'=>false,'show_ui'=>true,'show_in_menu'=>false,'show_in_rest'=>true,'supports'=>['title','editor','revisions'],'capabilities'=>['edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options','read_private_posts'=>'manage_options','delete_posts'=>'manage_options','create_posts'=>'manage_options'],'map_meta_cap'=>true]);
}
add_action('init','fm_delivery_types');

// Gutenberg needs REST; published project content must still remain staff-only.
add_filter('rest_pre_dispatch',function($result,$server,WP_REST_Request $request){
    if(preg_match('~^/wp/v2/fm_project_page(?:/|$)~',$request->get_route())&&!current_user_can('manage_options')){
        return new WP_Error('fm_project_private','Project pages are private.',['status'=>403]);
    }
    return $result;
},10,3);

function fm_delivery_design(int $id): array
{
    if(get_post_type($id)!=='fm_design'){throw new RuntimeException('Unknown design.');}
    $data=(array)get_post_meta($id,'_fm_design',true);
    if(empty($data['root']) || !is_dir($data['root'])){throw new RuntimeException('Design files are missing.');}
    return $data;
}

function fm_delivery_register_design(array $design): void
{
    foreach(glob($design['plugin'].'/blocks/*/block.json')?:[] as $file){$meta=FM_Delivery_Bundle::json($file);if(!WP_Block_Type_Registry::get_instance()->is_registered($meta['name'])){register_block_type(dirname($file));}}
}
add_action('init',function():void {
    foreach(['site-header','site-footer'] as $name){if(!WP_Block_Type_Registry::get_instance()->is_registered('foundations/'.$name)){register_block_type(FM_DELIVERY_DIR.'runtime/base/blocks/'.$name);}}
    foreach(get_posts(['post_type'=>'fm_design','post_status'=>'any','numberposts'=>-1]) as $p){$d=get_post_meta($p->ID,'_fm_design',true);if(is_array($d)&&!empty($d['plugin'])){fm_delivery_register_design($d);}}
},20);

/** Upload is admin-only: code in design packages has the same trust boundary as plugins. */
function fm_delivery_add_design(string $zipFile): int
{
    $root=FM_Delivery_Bundle::unzip($zipFile,true);
    $nested=[];$id=0;$previewId=0;
    try {
        $release=FM_Delivery_Bundle::json($root.'/release.json');
        $slug=$release['slug']??'';
        if(!$slug||sanitize_title($slug)!==$slug){throw new RuntimeException('Invalid design slug.');}
        if(get_page_by_path($slug,OBJECT,'fm_design')){throw new RuntimeException('This design slug already exists. Keep existing releases immutable; use a versioned slug for a new release.');}
        foreach(['theme.zip','plugin.zip','content.zip'] as $file){if(!isset($release['files'][$file])||!hash_equals($release['files'][$file],hash_file('sha256',$root.'/'.$file))){throw new RuntimeException('Checksum mismatch: '.$file);}}
        $themeRoot=FM_Delivery_Bundle::unzip($root.'/theme.zip',true);$nested[]=$themeRoot;
        $pluginRoot=FM_Delivery_Bundle::unzip($root.'/plugin.zip',true);$nested[]=$pluginRoot;
        $contentRoot=FM_Delivery_Bundle::unzip($root.'/content.zip');$nested[]=$contentRoot;
        $theme=$themeRoot.'/foundations-'.$slug;$plugin=$pluginRoot.'/foundations-site';
        if(!is_file($theme.'/theme.json')||!is_file($plugin.'/foundations-site.php')){throw new RuntimeException('Unexpected theme/plugin package structure.');}
        $id=wp_insert_post(['post_type'=>'fm_design','post_title'=>sanitize_text_field($release['name']),'post_name'=>$slug,'post_status'=>'draft'],true);
        if(is_wp_error($id)){throw new RuntimeException($id->get_error_message());}
        $data=['root'=>$root,'theme'=>$theme,'plugin'=>$plugin,'content'=>$contentRoot,'release'=>$release,'nested'=>$nested];
        update_post_meta($id,'_fm_design',wp_slash($data));
        fm_delivery_register_design($data);
        $content=FM_Delivery_Bundle::import($contentRoot,'fm_project_page',$id);
        update_post_meta($id,'_fm_content',wp_slash($content));
        if(!empty($release['preview'])){
            $preview=FM_Delivery_Bundle::path($root,$release['preview']);
            if(!is_file($preview)||!hash_equals($release['files'][$release['preview']]??'',hash_file('sha256',$preview))){throw new RuntimeException('Preview checksum mismatch.');}
            $tmp=wp_tempnam(basename($preview));copy($preview,$tmp);
            $previewId=media_handle_sideload(['name'=>basename($preview),'tmp_name'=>$tmp],$id);
            if(is_wp_error($previewId)){if(is_file($tmp)){unlink($tmp);}throw new RuntimeException($previewId->get_error_message());}
            update_post_meta($id,'_fm_preview_image',$previewId);
        }
        return $id;
    }catch(Throwable $e){if(is_int($previewId)&&$previewId){wp_delete_attachment($previewId,true);}if($id){$content=get_post_meta($id,'_fm_content',true);foreach($content['pages']??[] as $page){wp_delete_post($page,true);}foreach($content['media']??[] as $media){wp_delete_attachment($media,true);}wp_delete_post($id,true);}foreach($nested as $dir){FM_Delivery_Bundle::remove($dir);}FM_Delivery_Bundle::remove($root);throw $e;}
}

function fm_delivery_create_project(int $designId, string $name, string $customer, string $brief, int $order = 0): int
{
    $design=fm_delivery_design($designId);$master=(array)get_post_meta($designId,'_fm_content',true);
    $root=FM_Delivery_Bundle::export($master['pages'],$master['settings'],$master['manifest']);
    $id=wp_insert_post(['post_type'=>'fm_project','post_status'=>'private','post_title'=>$name?:'Customer project'],true);
    if(is_wp_error($id)){FM_Delivery_Bundle::remove($root);throw new RuntimeException($id->get_error_message());}
    try{
        $content=FM_Delivery_Bundle::import($root,'fm_project_page',$id);
        update_post_meta($id,'_fm_content',wp_slash($content));
        update_post_meta($id,'_fm_project',wp_slash(['design'=>$designId,'customer'=>sanitize_text_field($customer),'brief'=>sanitize_textarea_field($brief),'order'=>$order,'keycard'=>wp_generate_uuid4(),'created_at'=>gmdate('c'),'status'=>'Editing','deployments'=>[],'releases'=>[]]));
    }catch(Throwable $e){wp_delete_post($id,true);throw $e;}finally{FM_Delivery_Bundle::remove($root);}
    return $id;
}

function fm_delivery_release(int $id): array
{
    $project=(array)get_post_meta($id,'_fm_project',true);
    if(!$project){throw new RuntimeException('Unknown customer project.');}
    $design=fm_delivery_design((int)$project['design']);$content=(array)get_post_meta($id,'_fm_content',true);
    $root=FM_Delivery_Bundle::export($content['pages'],$content['settings'],$content['manifest']);
    $releaseId=wp_generate_uuid4();
    file_put_contents($root.'/keycard.json',wp_json_encode(['id'=>$project['keycard'],'release'=>$releaseId]));
    $stage=FM_Delivery_Bundle::private_dir().'/release-'.$releaseId;wp_mkdir_p($stage);
    FM_Delivery_Bundle::zip($root,$stage.'/content.zip');FM_Delivery_Bundle::remove($root);
    foreach(['theme.zip','plugin.zip'] as $file){copy($design['root'].'/'.$file,$stage.'/'.$file);}
    copy($design['root'].'/INSTALL.txt',$stage.'/INSTALL.txt');
    if(!empty($design['release']['preview'])){$preview=$design['release']['preview'];copy(FM_Delivery_Bundle::path($design['root'],$preview),FM_Delivery_Bundle::path($stage,$preview));}
    $release=$design['release'];$release['id']=$releaseId;$release['created_at']=gmdate('c');$release['keycard']=$project['keycard'];
    foreach(['theme.zip','plugin.zip','content.zip'] as $file){$release['files'][$file]=hash_file('sha256',$stage.'/'.$file);}
    file_put_contents($stage.'/release.json',wp_json_encode($release,JSON_PRETTY_PRINT));
    $zip=FM_Delivery_Bundle::private_dir().'/'.$releaseId.'.zip';FM_Delivery_Bundle::zip($stage,$zip);FM_Delivery_Bundle::remove($stage);
    $record=['id'=>$releaseId,'created_at'=>$release['created_at'],'path'=>$zip,'sha256'=>hash_file('sha256',$zip)];
    $project['releases'][]=$record;$project['status']='Ready for installation';update_post_meta($id,'_fm_project',wp_slash($project));
    return $record;
}

add_action('admin_menu',function():void{add_menu_page('Foundations Delivery','Delivery','manage_options','fm-delivery','fm_delivery_screen','dashicons-portfolio',26);});
function fm_delivery_form(string $task, int $id = 0): void
{
    echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="fm_delivery"><input type="hidden" name="task" value="'.esc_attr($task).'"><input type="hidden" name="id" value="'.$id.'">';wp_nonce_field('fm_delivery');
}
function fm_delivery_screen(): void
{
    if(!current_user_can('manage_options')){wp_die('Not allowed');}
    echo '<div class="wrap"><h1>Foundations Delivery</h1><p>Master designs stay separate from each customer’s content. Keycards record delivery; they never switch websites off.</p>';
    $id=absint($_GET['project']??0);
    $masterId=absint($_GET['design']??0);
    if($masterId && get_post_type($masterId)==='fm_design') {
        $c=(array)get_post_meta($masterId,'_fm_content',true);
        echo '<h2>'.esc_html(get_the_title($masterId)).'</h2><p>Editing this master affects its demo and future projects. Existing customer projects retain their own content.</p><ul>';
        foreach($c['pages'] as $page){echo '<li><a href="'.esc_url(get_edit_post_link($page)).'">Edit '.esc_html(get_the_title($page)).'</a></li>';}
        echo '</ul>';fm_delivery_form('save-master',$masterId);fm_site_settings_fields($c['settings']);submit_button('Save master settings');echo '</form></div>';return;
    }
    if($id && get_post_type($id)==='fm_project'){
        $p=(array)get_post_meta($id,'_fm_project',true);$c=(array)get_post_meta($id,'_fm_content',true);
        echo '<h2>'.esc_html(get_the_title($id)).'</h2><p>Keycard: <code>'.esc_html($p['keycard']).'</code> · '.esc_html($p['status']).'</p>';
        echo '<p><a class="button" target="_blank" href="'.esc_url(add_query_arg('fm_project_preview',$id,home_url('/'))).'">Preview customer site</a></p><ul>';
        foreach($c['pages'] as $slug=>$page){echo '<li><a href="'.esc_url(get_edit_post_link($page)).'">Edit '.esc_html(get_the_title($page)).'</a></li>';}echo '</ul>';
        fm_delivery_form('add-page',$id);echo '<label>New page title <input name="title" required></label> <label>Page slug <input name="slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" required></label>';submit_button('Add page','secondary');echo '</form>';
        fm_delivery_form('save',$id);
        echo '<p><label>Customer <input name="customer" class="regular-text" value="'.esc_attr($p['customer']).'"></label></p><p><label>Order ID <input type="number" name="order" value="'.absint($p['order']).'"></label></p><p><label>Requested changes<br><textarea name="brief" rows="4" class="large-text">'.esc_textarea($p['brief']).'</textarea></label></p>';
        fm_site_settings_fields($c['settings']);submit_button('Save project settings');echo '</form>';
        fm_delivery_form('release',$id);submit_button('Build downloadable release');echo '</form><h2>Release history</h2><ul>';
        foreach(array_reverse($p['releases']) as $r){$url=wp_nonce_url(add_query_arg(['action'=>'fm_delivery_download','id'=>$id,'release'=>$r['id']],admin_url('admin-post.php')),'fm_download_'.$id);echo '<li>'.esc_html($r['created_at']).' <a href="'.esc_url($url).'">Download release</a> <code>'.esc_html(substr($r['sha256'],0,12)).'</code></li>';}echo '</ul>';
        echo '<h2>Import a customer’s exported changes</h2><p>Creates replacement project pages; the old pages remain available in revisions/history until reviewed.</p>';fm_delivery_form('replace-content',$id);echo '<input type="file" name="bundle" accept=".zip" required>';submit_button('Import edited content','secondary');echo '</form>';
        echo '<h2>Installations</h2>';foreach($p['deployments'] as $d){echo '<p>'.esc_html($d['domain'].' — '.$d['date'].' — '.$d['release']).'</p>';}
        fm_delivery_form('deployment',$id);echo '<label>Installed domain <input type="url" name="domain" required placeholder="https://customer.example"></label> <label>Release <select name="release">';foreach($p['releases'] as $r){echo '<option value="'.esc_attr($r['id']).'">'.esc_html($r['created_at']).'</option>';}echo '</select></label>';submit_button('Record installation','secondary');echo '</form></div>';return;
    }
    echo '<h2>Master designs</h2>';fm_delivery_form('upload');echo '<label>Compiled delivery ZIP <input type="file" name="bundle" accept=".zip" required></label>';submit_button('Add master design');echo '</form>';
    $archived = !empty($_GET['show_archived']);
    $archive_filter = $archived ? [] : [['key'=>'_fm_delivery_archived','compare'=>'NOT EXISTS']];
    echo '<p><a href="'.esc_url(admin_url('admin.php?page=fm-delivery'.($archived?'':'&show_archived=1'))).'">'.($archived?'Hide archived test records':'Show archived test records').'</a></p>';
    echo '<table class="widefat striped"><thead><tr><th>Design</th><th>Version</th><th>Demo</th><th>Visibility</th></tr></thead><tbody>';
    $designs=get_posts(['post_type'=>'fm_design','post_status'=>'any','numberposts'=>-1,'meta_query'=>$archive_filter]);
    foreach($designs as $d){$data=fm_delivery_design($d->ID);echo '<tr><td>'.'<a href="'.esc_url(admin_url('admin.php?page=fm-delivery&design='.$d->ID)).'">'.esc_html($d->post_title).'</a></td><td>'.esc_html($data['release']['version']).'</td><td><a target="_blank" href="'.esc_url(home_url('/templates/'.$d->post_name.'/demo/')).'">Preview</a></td><td>';fm_delivery_form('publish',$d->ID);echo '<button class="button">'.($d->post_status==='publish'?'Hide from catalogue':'Publish demo').'</button></form></td></tr>';}
    echo '</tbody></table><h2>Create customer project</h2>';fm_delivery_form('create');echo '<p><label>Design <select name="design">';foreach($designs as $d){echo '<option value="'.$d->ID.'">'.esc_html($d->post_title).'</option>';}echo '</select></label></p><p><input name="name" class="regular-text" placeholder="Project name" required></p><p><input name="customer" class="regular-text" placeholder="Customer name / reference" required></p><p><input type="number" name="order" placeholder="Order ID (optional)"></p><p><textarea name="brief" class="large-text" placeholder="Requested edits"></textarea></p>';submit_button('Create isolated customer project');echo '</form><h2>Customer projects</h2><table class="widefat striped"><tr><th>Project</th><th>Customer</th><th>Order</th><th>Status</th><th>Keycard</th></tr>';
    foreach(get_posts(['post_type'=>'fm_project','post_status'=>'any','numberposts'=>-1,'meta_query'=>$archive_filter]) as $p){$m=get_post_meta($p->ID,'_fm_project',true);echo '<tr><td><a href="'.esc_url(admin_url('admin.php?page=fm-delivery&project='.$p->ID)).'">'.esc_html($p->post_title).'</a></td><td>'.esc_html($m['customer']).'</td><td>'.absint($m['order']).'</td><td>'.esc_html($m['status']).'</td><td><code>'.esc_html($m['keycard']).'</code></td></tr>';}
    echo '</table></div>';
}

add_action('admin_post_fm_delivery',function():void{
    if(!current_user_can('manage_options')){wp_die('Not allowed','',['response'=>403]);}check_admin_referer('fm_delivery');
    $task=sanitize_key($_POST['task']??'');$id=absint($_POST['id']??0);
    try{
        if($task==='upload'||$task==='replace-content'){
            if(empty($_FILES['bundle']['tmp_name'])||!is_uploaded_file($_FILES['bundle']['tmp_name'])){throw new RuntimeException('Choose a ZIP file.');}
        }
        if($task==='upload'){if(!current_user_can('install_plugins')){throw new RuntimeException('Installing design code requires plugin installation permission.');}fm_delivery_add_design($_FILES['bundle']['tmp_name']);}
        elseif($task==='save-master'){fm_delivery_design($id);$GLOBALS['fm_delivery_context']=$id;$c=(array)get_post_meta($id,'_fm_content',true);$c['settings']=fm_site_sanitize_settings((array)wp_unslash($_POST['settings']??[]));update_post_meta($id,'_fm_content',wp_slash($c));wp_safe_redirect(admin_url('admin.php?page=fm-delivery&design='.$id));exit;}
        elseif($task==='create'){$id=fm_delivery_create_project(absint($_POST['design']),sanitize_text_field(wp_unslash($_POST['name'])),sanitize_text_field(wp_unslash($_POST['customer'])),sanitize_textarea_field(wp_unslash($_POST['brief']??'')),absint($_POST['order']??0));}
        elseif($task==='publish'){$d=fm_delivery_design($id);if(!empty($d['release']['fixture'])){throw new RuntimeException('Fixtures cannot be published.');}wp_update_post(['ID'=>$id,'post_status'=>get_post_status($id)==='publish'?'draft':'publish']);$id=0;}
        else{
            if(get_post_type($id)!=='fm_project'){throw new RuntimeException('Unknown project.');}
            $GLOBALS['fm_delivery_context']=$id;
            $p=(array)get_post_meta($id,'_fm_project',true);$c=(array)get_post_meta($id,'_fm_content',true);
            if($task==='save'){$p['customer']=sanitize_text_field(wp_unslash($_POST['customer']??''));$p['brief']=sanitize_textarea_field(wp_unslash($_POST['brief']??''));$p['order']=absint($_POST['order']??0);$c['settings']=fm_site_sanitize_settings((array)wp_unslash($_POST['settings']??[]));update_post_meta($id,'_fm_project',wp_slash($p));update_post_meta($id,'_fm_content',wp_slash($c));}
            elseif($task==='add-page'){
                $slug=sanitize_title(wp_unslash($_POST['slug']??''));$title=sanitize_text_field(wp_unslash($_POST['title']??''));
                if(!$slug||!$title||isset($c['pages'][$slug])||count($c['pages'])>=50){throw new RuntimeException('Choose a unique page slug and title (maximum 50 pages).');}
                $page=wp_insert_post(wp_slash(['post_type'=>'fm_project_page','post_parent'=>$id,'post_title'=>$title,'post_name'=>$slug,'post_status'=>'publish','post_author'=>get_current_user_id(),'post_content'=>'<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">'.esc_html($title).'</h1><!-- /wp:heading -->']),true);
                if(is_wp_error($page)){throw new RuntimeException($page->get_error_message());}
                update_post_meta($page,'_fm_content_slug',$slug);$c['pages'][$slug]=$page;update_post_meta($id,'_fm_content',wp_slash($c));
            }
            elseif($task==='release'){fm_delivery_release($id);}
            elseif($task==='replace-content'){
                $root=FM_Delivery_Bundle::unzip($_FILES['bundle']['tmp_name']);
                try {
                    $manifest=FM_Delivery_Bundle::json($root.'/manifest.json');
                    if(($manifest['design']??'')!==($c['manifest']['design']??'')){throw new RuntimeException('Export belongs to another design.');}
                    if(is_file($root.'/keycard.json')){$card=FM_Delivery_Bundle::json($root.'/keycard.json');if(!empty($card['id'])&&!hash_equals($p['keycard'],(string)$card['id'])){throw new RuntimeException('Keycard belongs to another customer project.');}}
                    $new=FM_Delivery_Bundle::import($root,'fm_project_page',$id);
                    try{$new['settings']=fm_site_sanitize_settings($new['settings']);}
                    catch(Throwable $e){foreach($new['pages'] as $page){wp_delete_post($page,true);}foreach($new['media'] as $media){wp_delete_attachment($media,true);}throw $e;}
                    add_post_meta($id,'_fm_content_history',wp_slash($c));update_post_meta($id,'_fm_content',wp_slash($new));
                } finally {FM_Delivery_Bundle::remove($root);}
            }
            elseif($task==='deployment'){$release=sanitize_text_field($_POST['release']??'');if(!in_array($release,array_column($p['releases'],'id'),true)){throw new RuntimeException('Choose a release.');}$p['deployments'][]=['id'=>wp_generate_uuid4(),'domain'=>esc_url_raw(wp_unslash($_POST['domain']??'')),'date'=>gmdate('c'),'release'=>$release];$p['status']='Installed';update_post_meta($id,'_fm_project',wp_slash($p));}
            else{throw new RuntimeException('Unknown action.');}
        }
    }catch(Throwable $e){wp_die(esc_html($e->getMessage()));}
    wp_safe_redirect(admin_url('admin.php?page=fm-delivery'.($id?'&project='.$id:'')));exit;
});
add_action('admin_post_fm_delivery_download',function():void{
    if(!current_user_can('manage_options')){wp_die('Not allowed','',['response'=>403]);}$id=absint($_GET['id']??0);check_admin_referer('fm_download_'.$id);
    $p=(array)get_post_meta($id,'_fm_project',true);foreach($p['releases']??[] as $r){if(hash_equals($r['id'],(string)($_GET['release']??''))&&is_file($r['path'])){nocache_headers();header('Content-Type: application/zip');header('X-Content-Type-Options: nosniff');header('Content-Disposition: attachment; filename="customer-release-'.sanitize_file_name($r['id']).'.zip"');header('Content-Length: '.filesize($r['path']));readfile($r['path']);exit;}}
    wp_die('Release not found','',['response'=>404]);
});
