<?php
/** Local integration tests. All mail, HTTP and scheduled integrations are intercepted. */
if(PHP_SAPI!=='cli'||count($argv)!==2){exit("CLI: wp-load.php\n");}
define('WP_HTTP_BLOCK_EXTERNAL',true);define('DISABLE_WP_CRON',true);require $argv[1];
if(!str_ends_with(wp_parse_url(home_url(),PHP_URL_HOST),'.local')){throw new RuntimeException('Local sites only');}
$mail=[];$httpMode='failure';
add_filter('pre_wp_mail',function($pre,$args)use(&$mail){$mail[]=$args['subject'];return true;},PHP_INT_MAX,2);
add_filter('pre_http_request',function()use(&$httpMode){return $httpMode==='success'?['headers'=>[],'body'=>'{"ok":true}','response'=>['code'=>200,'message'=>'OK'],'cookies'=>[]]:new WP_Error('stub','Simulated calendar outage');},PHP_INT_MAX);
add_filter('pre_schedule_event',fn()=>false,PHP_INT_MAX);
add_filter('pre_option_fmcd_calendar_endpoint',fn()=>'https://calendar.invalid/test');
$request=function($route,$data){$r=new WP_REST_Request('POST','/fmcd/v1/'.$route);$r->set_body_params($data);return rest_do_request($r);};
$ids=[];$token=wp_generate_uuid4();
try{
    $data=['type'=>'contact','full_name'=>'Local acceptance test','email'=>'fixture@example.invalid','about'=>'Test only','request_id'=>$token,'hosting_password'=>'DO-NOT-STORE'];
    $first=$request('submit',$data);$body=$first->get_data();if($first->get_status()!==200||empty($body['id'])){throw new RuntimeException(wp_json_encode($body));}$ids[]=$body['id'];
    $again=$request('submit',$data)->get_data();if($again['id']!==$body['id']||count($mail)!==1){throw new RuntimeException('Retry duplicated a submission or mail');}
    if(isset(get_post_meta($body['id'],'_fmcd_payload',true)['hosting_password'])){throw new RuntimeException('Password was stored');}
    $bad=$request('submit',['request_id'=>wp_generate_uuid4(),'type'=>'contact','email'=>'invalid']);if($bad->get_status()!==400){throw new RuntimeException('Invalid input accepted');}
    $booking=['full_name'=>'Local acceptance test','email'=>'fixture@example.invalid','startIso'=>gmdate('c',time()+DAY_IN_SECONDS),'endIso'=>gmdate('c',time()+DAY_IN_SECONDS+3600),'request_id'=>wp_generate_uuid4()];
    $failed=$request('book',$booking)->get_data();if(!empty($failed['ok'])||count($mail)!==1){throw new RuntimeException('Failed booking was confirmed');}
    $httpMode='success';$success=$request('book',$booking)->get_data();if(empty($success['ok'])||count($mail)!==2){throw new RuntimeException('Confirmed calendar response not handled');}
    $request('book',$booking);if(count($mail)!==2){throw new RuntimeException('Retry duplicated confirmation');}
    echo "Contact integration: saved enquiry, idempotent retry, validation, password exclusion, failed booking, confirmed booking and retry passed. No external mail or HTTP sent.\n";
}finally{
    foreach($ids as $id){wp_delete_post($id,true);}
    foreach(get_posts(['post_type'=>'fmcd_entry','post_status'=>'any','numberposts'=>-1,'s'=>'Local acceptance test']) as $p){if(str_contains($p->post_title,'Local acceptance test')){wp_delete_post($p->ID,true);}}
}
