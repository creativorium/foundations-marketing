<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="fm-pa-privacy__section-1" data-pa-part="privacy" data-pa-view="privacy" hidden>
    <div class="fm-pa-privacy__div-2"><?= esc_html($fields['legal']); ?></div>
    <h1 class="fm-pa-privacy__h1-3"><?= esc_html($fields['privacy_policy']); ?></h1>
    <p class="fm-pa-privacy__p-4"><?= esc_html($fields['last_updated_12_august_2026']); ?></p>
    <?php foreach ($lists['privacy'] as $p_index => $p): ?>
      <div class="fm-pa-privacy__div-5">
        <h2 class="fm-pa-privacy__h2-6"><span><?= esc_html($p['h']); ?></span></h2>
        <p class="fm-pa-privacy__p-7"><span><?= esc_html($p['b']); ?></span></p>
      </div>
    <?php endforeach; ?>
    <div class="fm-pa-privacy__div-8"><?= esc_html($fields['questions_about_any_of_this_email_hello_pulsepil']); ?></div>
  </section>
