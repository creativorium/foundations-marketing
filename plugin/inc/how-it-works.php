<?php
/** Approved marketing process page; shares the site's header, footer and tokens. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_how_it_works_shortcode(): string
{
    $content = json_decode((string) file_get_contents(__DIR__ . '/content/how-it-works.json'), true);
    $revision_url = (string) get_option('fm_revision_policy_url', '');
    $checklist_url = (string) get_option('fm_content_checklist_url', '');
    $image_root = FM_BLOCKS_URL . 'assets/images/marketing/';
    ob_start();
    ?>
    <article class="fm-how" id="top">
      <header class="fm-how__hero">
        <div>
          <span class="fm-how__pill">How it works</span>
          <h1>Here's<br>how it<br><span>works.</span></h1>
          <p>No jargon, no contracts, and no more nights hunched over a laptop moving a button three pixels to the left.</p>
          <p>Here's the whole process, start to finish, so you know exactly what happens and when.</p>
          <div class="fm-how__actions"><?php echo fm_marketing_cta('See the templates →', '/templates/'); ?><?php echo fm_marketing_cta('Book a free 20-min call', '/contact-page/', false); ?></div>
          <p class="fm-how__note">One revision round · Yours to keep</p>
        </div>
        <img class="fm-how__hero-photo" src="<?php echo esc_url($image_root . 'how-it-works-hero.webp'); ?>" alt="Someone browsing the Halo website template on a laptop" width="900" height="720" fetchpriority="high" decoding="async">
      </header>
      <div class="fm-story__ticker" aria-hidden="true"><div class="fm-story__ticker-track"><?php for ($i = 0; $i < 2; $i++) : ?><span>Pick a template — Add what you need — Get your guide — Fill in the form — We build it — Check it, tweak it — Go live —</span><?php endfor; ?></div></div>
      <section class="fm-how__section" aria-labelledby="process-title">
        <div class="fm-how__label"><span>The process</span><span>Seven steps ↓</span></div>
        <div class="fm-how__intro"><h2 id="process-title">You do three<br>things. We do<br>the rest.</h2><p>We've kept this deliberately simple. You do three things: pick a template, send us your content, and check the preview. We do everything else. Most people spend an hour or two on their side of it, spread across a week — and most of that is writing about their own business, which nobody can do better than you.</p></div>
        <ol class="fm-how__steps">
          <?php foreach ($content['steps'] as $index => $step) :
              $html = $step['html'];
              $policy = $revision_url !== '' ? '<a href="' . esc_url($revision_url) . '">revision policy</a>' : 'revision policy';
              $html = str_replace('<a href="#">revision policy</a>', $policy, $html);
              ?>
            <li class="fm-how__step"><div><span class="fm-how__number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span><h3><?php echo esc_html($step['title']); ?></h3><?php if ($step['badge'] !== '') : ?><span class="fm-how__pill"><?php echo esc_html($step['badge']); ?></span><?php endif; ?></div><div class="fm-how__copy"><?php echo wp_kses_post($html); ?></div></li>
          <?php endforeach; ?>
        </ol>
      </section>
      <section class="fm-how__section" aria-labelledby="checklist-title">
        <div class="fm-how__label"><span>Your checklist</span><span>Before you start</span></div>
        <div class="fm-how__checklist-grid"><div><h2 id="checklist-title">Before<br><span>you start</span></h2><p>We send you a checklist before we start. Use it to gather everything — we only begin building once it's all in. Here's what it covers:</p><?php if ($checklist_url !== '') : ?><a class="fm-story__button" href="<?php echo esc_url($checklist_url); ?>">Download the checklist</a><?php else : ?><button class="fm-story__button fm-how__pending" type="button" disabled aria-describedby="checklist-pending">Download the checklist</button><small id="checklist-pending">Available soon</small><?php endif; ?></div><ul class="fm-how__checklist"><?php foreach ($content['checklist'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul></div>
      </section>
      <section class="fm-how__section" aria-labelledby="honest-title">
        <div class="fm-how__label"><span>Straight answers</span><span>Read this bit ↓</span></div>
        <h2 id="honest-title" class="fm-how__section-title">What we're<br><span>honest about</span></h2>
        <div class="fm-how__honest"><?php foreach ($content['honest'] as $item) : ?><div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['text']); ?></p></div><?php endforeach; ?></div>
      </section>
      <section class="fm-how__section fm-how__founders" aria-labelledby="founders-title">
        <div class="fm-how__label"><span>The founders</span><span>Who builds it</span></div>
        <div class="fm-how__founder-card"><img src="<?php echo esc_url($image_root . 'how-it-works-founders.webp'); ?>" alt="Chia and Ralu, the founders of Foundations Marketing" width="1200" height="800" loading="lazy" decoding="async"><div><span class="fm-how__eyebrow">The people behind it</span><h2 id="founders-title">That's us —<br>Chia and Ralu.</h2><p>Two founders, two mums, one belief: you shouldn't have to fight your own website to get your business off the ground.</p><p>Every site that goes through this process goes through our hands.</p><a href="<?php echo esc_url(home_url('/who-are-we/')); ?>">Read our story →</a></div></div>
      </section>
      <aside class="fm-how__closing"><h2>One clear page. Five working days. <span>Yours to keep.</span></h2><p>Not sure which template, or whether this is right for you? Book a free 20-minute call with Chia.</p><p>No pitch, no pressure — just a chat about what you're building and whether we can help. If we can't, we'll say so.</p><div class="fm-how__actions"><?php echo fm_marketing_cta('Choose your template →', '/templates/'); ?><?php echo fm_marketing_cta('Book a free 20-min call', '/contact-page/', false); ?></div><p class="fm-how__note">Delivered in 5 days · Revision included · No contracts</p></aside>
    </article>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('fm_how_it_works', 'fm_how_it_works_shortcode');

/** Run once on each install, without resetting the other marketing pages. */
function fm_install_how_it_works(): void
{
    if (get_option('fm_how_it_works_version') === '2') {
        return;
    }
    $page = get_page_by_path('how-it-works', OBJECT, 'page');
    $data = ['post_title' => 'How it works', 'post_content' => '[fm_how_it_works]', 'post_status' => 'publish'];
    if ($page instanceof WP_Post) {
        fm_marketing_update_post($page->ID, $data);
        $id = $page->ID;
        foreach (['_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_wp_page_template'] as $key) {
            delete_post_meta($id, $key);
        }
    } else {
        $id = wp_insert_post($data + ['post_type' => 'page', 'post_name' => 'how-it-works'], true);
    }
    if (is_wp_error($id) || !$id) {
        return;
    }
    // Replace the old homepage anchors in assigned menus with the actual pages.
    foreach (array_unique(array_values(get_nav_menu_locations())) as $menu_id) {
        foreach ((array) wp_get_nav_menu_items($menu_id) as $item) {
            $label = strtolower(trim(wp_strip_all_tags((string) $item->title)));
            $slug = match ($label) {
                'how it works' => 'how-it-works',
                'faq', 'faqs', 'frequently asked questions' => 'frequently-asked-questions',
                default => '',
            };
            if ($slug === '') {
                continue;
            }
            $destination = get_page_by_path($slug, OBJECT, 'page');
            if (!$destination instanceof WP_Post) {
                continue;
            }
            update_post_meta($item->ID, '_menu_item_type', 'post_type');
            update_post_meta($item->ID, '_menu_item_object', 'page');
            update_post_meta($item->ID, '_menu_item_object_id', $destination->ID);
            update_post_meta($item->ID, '_menu_item_url', get_permalink($destination));
        }
    }
    update_option('fm_how_it_works_version', '2', false);
}
add_action('init', 'fm_install_how_it_works', 32);
