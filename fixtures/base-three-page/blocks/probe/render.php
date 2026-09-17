<?php
if (!defined('ABSPATH')) { exit; }
$page=(int)($attributes['targetPageId']??0);
?>
<div <?php echo fm_wrapper(['fm-fixture-probe']); ?>>
    <?php echo fm_image((int)($attributes['imageId']??0),'thumbnail',['class'=>'fm-fixture-probe__image']); ?>
    <a href="<?php echo esc_url($page?get_permalink($page):home_url('/')); ?>"><?php echo esc_html($attributes['label']??''); ?></a>
</div>
