<?php
if(PHP_SAPI!=='cli'||count($argv)!==2){exit("CLI: wp-load.php\n");}
define('WP_HTTP_BLOCK_EXTERNAL',true);define('DISABLE_WP_CRON',true);require $argv[1];
if(!function_exists('fm_builder_config')){throw new RuntimeException('Builder plugin is inactive');}
$config=fm_builder_config();$products=wc_get_products(['limit'=>1]);if(!$products){throw new RuntimeException('No product available for read-only price test');}
$product=$products[0];$base=(float)$product->get_price();
$cart=new class extends WC_Cart {public $testItems=[];public function __construct(){}public function get_cart(){return $this->testItems;}};
$cart->testItems=[['data'=>clone $product,'product_id'=>$product->get_id(),'variation_id'=>0,'fm_extras'=>[['price'=>50],['price'=>75]]]];
fm_builder_apply_prices($cart);$one=(float)$cart->testItems[0]['data']->get_price();fm_builder_apply_prices($cart);$two=(float)$cart->testItems[0]['data']->get_price();
if($one!==$base+125||$two!==$one){throw new RuntimeException('Extras compounded');}
$addon=$config['addons'][0]??null;if(!$addon){throw new RuntimeException('Builder defaults were not loaded');}
$_POST=['fm_build'=>1,'fm_build_nonce'=>wp_create_nonce('fm_build_order'),'fm_addons'=>[$addon['id'],$addon['id']]];
$item=fm_builder_cart_item_data([]);if(count($item['fm_extras'])!==1){throw new RuntimeException('Duplicate extras accepted');}
echo wp_json_encode(['passed'=>true,'builder_page'=>(int)get_option('fm_builder_page_id'),'default_addons'=>count($config['addons']),'extras_deduplicated'=>true,'repeated_price_stable'=>true]);
