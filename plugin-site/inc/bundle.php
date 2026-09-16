<?php
/** Portable content only. No executable code, users, orders or arbitrary options. */
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }

final class FM_Delivery_Bundle
{
    public static function private_dir(): string
    {
        $base = dirname(rtrim(ABSPATH, '/\\')) . '/fm-delivery-private';
        if (!is_dir($base) && !wp_mkdir_p($base)) { throw new RuntimeException('Cannot create private delivery storage outside the public directory.'); }
        $real = realpath($base);
        $public = realpath(ABSPATH);
        if (!$real || !$public || str_starts_with(str_replace('\\','/', $real) . '/', str_replace('\\','/', $public) . '/')) { throw new RuntimeException('Delivery storage must be outside the public directory.'); }
        return wp_normalize_path($real);
    }

    public static function json(string $file): array
    {
        if (!is_file($file)) { throw new RuntimeException('Missing ' . basename($file)); }
        $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) { throw new RuntimeException('Expected an object in ' . basename($file)); }
        return $data;
    }

    public static function path(string $root, string $relative): string
    {
        if ($relative === '' || preg_match('~(^/|\\\\|[:*?"<>|]|(?:^|/)\.{1,2}(?:/|$)|[^/][. ](?:/|$)|\x00)~', $relative)) { throw new RuntimeException('Unsafe bundle path.'); }
        return rtrim($root, '/\\') . '/' . $relative;
    }

    /** Bounded, traversal-safe extraction to a unique private directory. */
    public static function unzip(string $file, bool $code = false): string
    {
        if (!class_exists('ZipArchive')) { throw new RuntimeException('PHP ZIP extension is required.'); }
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) { throw new RuntimeException('Not a ZIP file.'); }
        $root = self::private_dir() . '/unpack-' . wp_generate_uuid4();
        $total = 0;
        try {
            if ($zip->numFiles > 3000) { throw new RuntimeException('Bundle contains too many files.'); }
            for ($i=0; $i<$zip->numFiles; $i++) {
                $stat=$zip->statIndex($i); $name=$stat['name']; self::path($root,$name);
                $total += $stat['size'];
                if ($total > 128*1024*1024 || $stat['size'] > 32*1024*1024) { throw new RuntimeException('Bundle exceeds the 128 MB expanded limit.'); }
                if (str_ends_with($name, '/')) { continue; }
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $code ? ['php','js','css','json','html','txt','png','jpg','jpeg','webp','gif','svg','woff','woff2','ttf','otf','zip','md'] : ['json','txt','png','jpg','jpeg','webp','gif','pdf'], true)) { throw new RuntimeException('Unsupported file: ' . $name); }
            }
            wp_mkdir_p($root);
            for ($i=0; $i<$zip->numFiles; $i++) {
                $name=$zip->getNameIndex($i); if (str_ends_with($name,'/')) { continue; }
                $target=self::path($root,$name);wp_mkdir_p(dirname($target));
                if (file_put_contents($target,$zip->getFromIndex($i)) === false) { throw new RuntimeException('Could not write bundle file.'); }
            }
        } catch (Throwable $e) { self::remove($root); throw $e; }
        finally { $zip->close(); }
        return $root;
    }

    public static function remove(string $root): void
    {
        $base = str_replace('\\','/', self::private_dir()) . '/';
        $real = realpath($root);
        if (!$real || !str_starts_with(str_replace('\\','/',$real) . '/', $base) || str_replace('\\','/',$real) . '/' === $base) { return; }
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $file) { if($file->isDir() && !$file->isLink()) { rmdir($file->getPathname()); } else { unlink($file->getPathname()); } }
        rmdir($real);
    }

    public static function zip(string $root, string $target): void
    {
        $zip=new ZipArchive();if($zip->open($target,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) { throw new RuntimeException('Cannot create release ZIP.'); }
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach($it as $file) { if($file->isFile() && !$file->isLink()) { $zip->addFile($file->getPathname(),str_replace('\\','/',substr($file->getPathname(),strlen($root)+1))); } }
        $zip->close();
    }

    public static function resolve(mixed $value, array $pages, array $media, bool $html = false): mixed
    {
        if (is_array($value)) { foreach($value as $key=>$v) { $value[$key]=self::resolve($v,$pages,$media,$html); } return $value; }
        if (!is_string($value)) { return $value; }
        $single = preg_match('/^\{\{(media|page):([^|{}]+)\|id\}\}$/',$value);
        $out=preg_replace_callback('/\{\{(media|page):([^|{}]+)\|(id|url)\}\}/',function($m)use($pages,$media,$html){
            $map=$m[1]==='page'?$pages:$media;
            if(!isset($map[$m[2]])) { throw new RuntimeException('Unresolved reference: '.$m[0]); }
            $id=$map[$m[2]];
            if($m[3]==='id') { return (string)$id; }
            $url=$m[1]==='page'?get_permalink($id):wp_get_attachment_url($id);
            if(!$url) { throw new RuntimeException('Missing URL for '.$m[0]); }
            return $html?esc_url($url):$url;
        },$value);
        if(str_contains($out,'{{')) { throw new RuntimeException('Malformed or unresolved token: '.$value); }
        return $single?(int)$out:$out;
    }

    public static function resolve_content(string $content, array $pages, array $media): string
    {
        // Attribute JSON has different escaping and types from the saved HTML.
        $content=preg_replace_callback('/<!--\s+wp:([a-z0-9\/-]+)\s+(\{.*?\})\s*(\/)?-->/s',function($m)use($pages,$media){
            $attrs=json_decode($m[2],true,512,JSON_THROW_ON_ERROR);
            return '<!-- wp:'.$m[1].' '.serialize_block_attributes(self::resolve($attrs,$pages,$media)).' '.(!empty($m[3])?'/':'').'-->';
        },$content);
        return (string) self::resolve($content,$pages,$media,true);
    }

    /** New pages only. Caller owns project isolation and stores returned IDs. */
    public static function import(string $root, string $type = 'page', int $parent = 0, bool $configureSite = false): array
    {
        $manifest=self::json($root.'/manifest.json');
        $specs=$manifest['pages']??[];
        if(!$specs || count($specs)>50) { throw new RuntimeException('Bundle needs 1–50 pages.'); }
        $sources=[];$slugs=[];
        foreach($specs as $spec) {
            $slug=$spec['slug']??'';
            $status=$spec['status']??'publish';
            if(!in_array($status,['publish','draft','private'],true)){throw new RuntimeException('Unsupported page status.');}
            if($slug===($manifest['homepage']??'')&&$status!=='publish'){throw new RuntimeException('Homepage must be published.');}
            if(!$slug || sanitize_title($slug)!==$slug || isset($slugs[$slug])) { throw new RuntimeException('Invalid or duplicate page slug.'); }
            if($type==='page' && get_page_by_path($slug)) { throw new RuntimeException('Page already exists: '.$slug.'. Import into a fresh site.'); }
            $slugs[$slug]=true;
            $file=self::path($root,$spec['file']);
            if(!str_ends_with($file,'.blocks.txt') || !is_file($file)) { throw new RuntimeException('Missing page file: '.$spec['file']); }
            $sources[$slug]=(string)file_get_contents($file);
            $walk=function(array $blocks)use(&$walk){foreach($blocks as $b){if($b['blockName']===null && trim($b['innerHTML'])!==''){throw new RuntimeException('Raw HTML outside a block.');} if($b['blockName'] && !WP_Block_Type_Registry::get_instance()->is_registered($b['blockName'])) {throw new RuntimeException('Install the required block: '.$b['blockName']);} $walk($b['innerBlocks']);}};
            $walk(parse_blocks($sources[$slug]));
        }
        if(!isset($slugs[$manifest['homepage']??''])) { throw new RuntimeException('Homepage is not in the page list.'); }
        $settings=self::json(self::path($root,$manifest['settings']??'settings.json'));unset($settings['$comment']);
        $nav=self::json(self::path($root,$manifest['navigation']??'navigation.json'));unset($nav['$comment']);
        $mediaFiles=glob($root.'/media/*')?:[];
        $mediaSpec=self::json(self::path($root,$manifest['media']??'media.json'));
        $mediaDetails=[];foreach($mediaSpec['files']??[] as $row){if(is_array($row)&&isset($row['file'])){$mediaDetails[$row['file']]=$row;}}
        $knownMedia=[];foreach($mediaFiles as $file){$knownMedia[basename($file)]=true;}
        foreach(array_merge($sources,['settings.json'=>wp_json_encode($settings),'navigation.json'=>wp_json_encode($nav)]) as $name=>$source) {
            preg_match_all('/\{\{.*?(?:\}\}|$)/s',$source,$tokens,PREG_OFFSET_CAPTURE);
            foreach($tokens[0] as [$token,$offset]) {
                if(!preg_match('/^\{\{(media|page):([^|{}\s]+)\|(id|url)\}\}$/',$token,$m) || !isset(($m[1]==='media'?$knownMedia:$slugs)[$m[2]])) { throw new RuntimeException($name.':'.(substr_count(substr($source,0,$offset),"\n")+1).' unknown token '.$token); }
            }
        }
        require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
        $pages=[];$media=[];$previousOptions=[];
        try {
            foreach($mediaFiles as $file) {
                $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
                if(!in_array($ext,['png','jpg','jpeg','webp','gif','pdf'],true)) {throw new RuntimeException('Unsupported media.');}
                $tmp=wp_tempnam(basename($file));copy($file,$tmp);
                $id=media_handle_sideload(['name'=>basename($file),'tmp_name'=>$tmp],0);
                if(is_wp_error($id)){if(is_file($tmp)){unlink($tmp);}throw new RuntimeException($id->get_error_message());}
                $media[basename($file)]=$id;
                $details=$mediaDetails[basename($file)]??[];
                if($details){
                    wp_update_post(wp_slash(['ID'=>$id,'post_title'=>sanitize_text_field($details['title']??basename($file)),'post_excerpt'=>wp_kses_post($details['caption']??''),'post_content'=>wp_kses_post($details['description']??'')]));
                    update_post_meta($id,'_wp_attachment_image_alt',wp_slash(sanitize_text_field($details['alt']??'')));
                }
            }
            foreach($specs as $spec) {
                $id=wp_insert_post(['post_type'=>$type,'post_parent'=>$parent,'post_title'=>sanitize_text_field($spec['title']),'post_name'=>$spec['slug'],'post_status'=>$spec['status']??'publish','post_author'=>get_current_user_id()],true);
                if(is_wp_error($id)) {throw new RuntimeException($id->get_error_message());}
                $pages[$spec['slug']]=$id;
                update_post_meta($id,'_fm_content_slug',$spec['slug']);
            }
            if ($configureSite && $type === 'page') {
                foreach (['permalink_structure'=>'/%postname%/','show_on_front'=>'page','page_on_front'=>$pages[$manifest['homepage']]] as $key=>$value) {
                    $previousOptions[$key]=get_option($key);
                    update_option($key,$value);
                }
            }
            foreach($sources as $slug=>$source) {
                $content=self::resolve_content($source,$pages,$media);
                $updated=wp_update_post(wp_slash(['ID'=>$pages[$slug],'post_content'=>$content]),true);
                if(is_wp_error($updated)) {throw new RuntimeException($updated->get_error_message());}
                update_post_meta($pages[$slug],'_fm_content_slug',$slug);
            }
            $settings=self::resolve($settings,$pages,$media);
            $nav=self::resolve($nav,$pages,$media);
            foreach(['nav_primary','nav_footer'] as $key) {
                $map=function(array $rows)use(&$map,$pages):array {foreach($rows as &$r){if(!empty($r['page'])) {if(!isset($pages[$r['page']])){throw new RuntimeException('Unknown navigation page.');}$r['page']=$pages[$r['page']];}$r['children']=$map((array)($r['children']??[]));}return $rows;};
                $settings[$key]=$map((array)($nav[$key]??[]));
            }
            return ['pages'=>$pages,'media'=>$media,'settings'=>$settings,'manifest'=>$manifest,'homepage'=>$pages[$manifest['homepage']]];
        } catch(Throwable $e) {foreach($previousOptions as $key=>$value){update_option($key,$value);}foreach($pages as $id){wp_delete_post($id,true);}foreach($media as $id){wp_delete_attachment($id,true);}throw $e;}
    }

    /** Export only explicit pages, referenced uploads and whitelisted site settings. */
    public static function export(array $pages, array $settings, array $manifest): string
    {
        $root=self::private_dir().'/export-'.wp_generate_uuid4();wp_mkdir_p($root.'/pages');wp_mkdir_p($root.'/media');
        try {
        foreach(array_keys($pages) as $slug){if(!is_string($slug)||sanitize_title($slug)!==$slug){throw new RuntimeException('Invalid export page slug.');}}
        if(!$pages||count($pages)>50){throw new RuntimeException('Select 1–50 pages to export.');}
        $pageTokens=[];$urlTokens=[];$mediaTokens=[];$specs=[];$mediaDetails=[];
        foreach($pages as $slug=>$id){$post=get_post($id);if(!$post){throw new RuntimeException('Missing project page.');}$pageTokens[(int)$id]='{{page:'.$slug.'|id}}';$urlTokens[get_permalink($id)]='{{page:'.$slug.'|url}}';if($post->post_type==='page'){$urlTokens[add_query_arg('page_id',$id,home_url('/'))]='{{page:'.$slug.'|url}}';$urlTokens[home_url('/'.get_page_uri($id).'/')]='{{page:'.$slug.'|url}}';}$specs[]=['slug'=>$slug,'title'=>$post->post_title,'status'=>$post->post_status,'file'=>'pages/'.$slug.'.blocks.txt'];}
        $media=function(int $id)use(&$mediaTokens,&$urlTokens,&$mediaDetails,$root):void {
            if(isset($mediaTokens[$id]) || get_post_type($id)!=='attachment'){return;}
            $file=get_attached_file($id);$uploads=wp_get_upload_dir();
            if(!$file || !is_file($file) || !str_starts_with(wp_normalize_path(realpath($file)),wp_normalize_path(realpath($uploads['basedir'])).'/')){throw new RuntimeException('Referenced upload is missing.');}
            $name=$id.'-'.sanitize_file_name(basename($file));copy($file,$root.'/media/'.$name);
            $attachment=get_post($id);
            $mediaDetails[]=['file'=>$name,'title'=>$attachment->post_title,'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true),'caption'=>$attachment->post_excerpt,'description'=>$attachment->post_content];
            $mediaTokens[$id]='{{media:'.$name.'|id}}';$urlTokens[wp_get_attachment_url($id)]='{{media:'.$name.'|url}}';
            foreach((array)(wp_get_attachment_metadata($id)['sizes']??[]) as $size){$urlTokens[dirname(wp_get_attachment_url($id)).'/'.$size['file']]='{{media:'.$name.'|url}}';}
        };
        $collect=function(mixed $value,string $key='')use(&$collect,$media):void {
            if(is_array($value)){foreach($value as $k=>$v){$collect($v,(string)$k);}return;}
            if(is_numeric($value) && preg_match('/(^id$|Id$|_id$)/',$key)){ $media((int)$value); }
            if(is_string($value) && str_starts_with($value,wp_get_upload_dir()['baseurl'].'/')){$id=attachment_url_to_postid($value);if($id){$media($id);}}
        };
        $blocks=[];foreach($pages as $slug=>$id){$blocks[$slug]=parse_blocks(get_post($id)->post_content);$collect($blocks[$slug]);}
        $collect($settings);
        foreach($urlTokens as $url=>$token){$urlTokens[esc_url($url)]=$token;$urlTokens[esc_attr($url)]=$token;}
        uksort($urlTokens,fn($a,$b)=>strlen($b)<=>strlen($a));
        $patterns=[];
        foreach($urlTokens as $url=>$token){
            // The homepage must not match the prefix of an unrelated site URL.
            $tail=$url===home_url('/')?'(?=$|[\\s\\x22\\x27<>#])':'(?![A-Za-z0-9/_%.-])';
            $patterns[]=preg_quote($url,'~').$tail;
        }
        $urlPattern='~'.implode('|',$patterns).'~';
        $replace=function(mixed $v,string $key='')use(&$replace,$pageTokens,$mediaTokens,$urlTokens,$urlPattern):mixed {
            if(is_array($v)){foreach($v as $k=>$item){$v[$k]=$replace($item,(string)$k);}return $v;}
            if(is_numeric($v)){if(($key==='page'||preg_match('/(^id$|Id$|_id$)/',$key)) && isset($pageTokens[(int)$v])){return $pageTokens[(int)$v];}if(preg_match('/(^id$|Id$|_id$)/',$key)&&isset($mediaTokens[(int)$v])){return $mediaTokens[(int)$v];}}
            if(!is_string($v)){return $v;}
            $v=preg_replace_callback($urlPattern,fn($match)=>$urlTokens[$match[0]],$v);
            return preg_replace_callback('/wp-image-(\d+)/',fn($m)=>isset($mediaTokens[(int)$m[1]])?'wp-image-'.$mediaTokens[(int)$m[1]]:$m[0],$v);
        };
        foreach($blocks as $slug=>$data){file_put_contents($root.'/pages/'.$slug.'.blocks.txt',serialize_blocks($replace($data)));}
        $nav=[];foreach(['nav_primary','nav_footer'] as $key){$nav[$key]=$settings[$key]??[];unset($settings[$key]);}
        $navMap=function(array $rows)use(&$navMap,$pages):array {foreach($rows as &$row){if(!empty($row['page'])){$slug=array_search((int)$row['page'],$pages,true);if($slug===false){throw new RuntimeException('Navigation references a page outside this project.');}$row['page']=$slug;}if(isset($row['children'])){$row['children']=$navMap($row['children']);}}return $rows;};
        foreach($nav as &$rows){$rows=$navMap($rows);}unset($rows);
        file_put_contents($root.'/navigation.json',wp_json_encode($replace($nav),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        file_put_contents($root.'/settings.json',wp_json_encode($replace($settings),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $manifest['pages']=$specs;$manifest['settings']='settings.json';$manifest['navigation']='navigation.json';$manifest['media']='media.json';$manifest['exported_at']=gmdate('c');
        file_put_contents($root.'/manifest.json',wp_json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        file_put_contents($root.'/media.json',wp_json_encode(['files'=>$mediaDetails],JSON_PRETTY_PRINT));
        return $root;
        } catch(Throwable $e){self::remove($root);throw $e;}
    }
}
