<?php
/** Native About and FAQ pages for the Foundations Marketing site. */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_marketing_cta(string $label, string $path, bool $primary = true): string
{
    return sprintf(
        '<a class="fm-story__button%s" href="%s">%s</a>',
        $primary ? ' fm-story__button--primary' : '',
        esc_url(home_url($path)),
        esc_html($label)
    );
}

/** Update migration-owned page fields without firing unrelated legacy save webhooks. */
function fm_marketing_update_post(int $post_id, array $fields): void
{
    global $wpdb;
    $allowed = array_intersect_key($fields, array_flip(['post_title', 'post_name', 'post_content', 'post_status']));
    if ($allowed !== []) {
        $wpdb->update($wpdb->posts, $allowed, ['ID' => $post_id]);
        clean_post_cache($post_id);
    }
}

function fm_about_page_shortcode(): string
{
    $photo = FM_BLOCKS_URL . 'assets/images/chia-ralu.jpg';
    ob_start();
    ?>
    <article class="fm-story fm-story--about">
      <header class="fm-story__hero">
        <div class="fm-story__label"><span>Who are we</span><span>Chia &amp; Ralu</span></div>
        <h1>Built by two<br><span>mums who get it.</span></h1>
        <div class="fm-story__intro"><h2>Empowering entrepreneurs, one foundation at a time.</h2><div><p>Foundations Marketing was born from a shared experience. Two mothers, two founders, one clear belief: that you shouldn't have to fight your own website to get your business off the ground.</p><p>We are not a traditional agency. We're here to give you one thing, done properly: a clean, functional website that actually looks like you from day one.</p></div></div>
      </header>
      <div class="fm-story__ticker" aria-hidden="true"><span>Empowering entrepreneurs — one foundation at a time — empowering entrepreneurs — one foundation at a time —</span></div>

      <section class="fm-story__section">
        <div class="fm-story__section-label"><span>01 — Our story</span><span>Chia ↓</span></div>
        <div class="fm-story__profile">
          <div><h2>I know what it feels like</h2><img class="fm-story__portrait fm-story__portrait--chia" src="<?php echo esc_url($photo); ?>" alt="Chia and Ralu, co-founders of Foundations Marketing" loading="eager" decoding="async"></div>
          <div class="fm-story__copy"><p class="fm-story__lead">I'm Chia. And I know exactly what it feels like to start something from nothing.</p><p>When I qualified as a massage therapist, I had the training, the passion, and a real belief that I could build something meaningful. What I didn't have was the emotional bandwidth to stay with it once I graduated. Life happened. I became a sole parent, and suddenly building a business had to happen around school runs and the particular exhaustion that comes with doing everything on your own.</p><p>The website was one of those things I kept circling back to and walking away from. I remember nights past midnight, hunched over my laptop, trying to get one tiny detail right. The more I tweaked, the further away it felt from what I'd imagined.</p><p>You sign up, pick a beautiful template, then add your content and slowly it falls apart. By then, you're locked into monthly fees or annual contracts with no easy way out.</p><blockquote>A website is not the be all and end all. But it signals credibility. It tells someone who doesn't know you yet: this person is serious.</blockquote><p><strong>That's exactly what Foundations Marketing is here to fix.</strong></p></div>
        </div>
      </section>

      <section class="fm-story__section">
        <div class="fm-story__section-label"><span>02 — Our story</span><span>Ralu ↓</span></div>
        <div class="fm-story__profile fm-story__profile--reverse">
          <div class="fm-story__copy"><p class="fm-story__lead">I'm Ralu. And I've spent the last ten years on the other side of the table.</p><p>I opened my marketing agency in Bali a decade ago, and since then I've worked with more than 200 clients, building their websites, managing them, and learning what actually works versus what just looks good. Along the way I've grown a team of twenty in Indonesia.</p><p>Then I started working as a freelance florist. For the first time in years, I wasn't the established business. I was just a person with a skill, trying to show up as a professional. I saw how much harder it is to market yourself when you're starting from scratch.</p><p>Chia and I met in Bali more than ten years ago. She's my closest friend and my son's godmother. Foundations Marketing came out of our long conversations about why it is so hard for someone starting out to get the basics right.</p><p>We've gone over everything, from the templates to how easy they are to use, taking the friction out of the whole process at a price that makes sense when you're just getting started.</p><blockquote>It's your trusted friend with years of experience, at mates' rates.</blockquote><p><strong>I'm so excited to build wonderful websites for passionate people who are just getting going.</strong></p></div>
          <div><h2>Ten years on the other side</h2><img class="fm-story__portrait fm-story__portrait--ralu" src="<?php echo esc_url($photo); ?>" alt="Chia and Ralu, co-founders of Foundations Marketing" loading="lazy" decoding="async"></div>
        </div>
      </section>

      <section class="fm-story__section">
        <div class="fm-story__section-label"><span>03 — What we built</span><span>Two mothers, two founders ↓</span></div>
        <div class="fm-story__split"><h2>One thing,<br><span>done properly</span></h2><div class="fm-story__copy"><p>We built Foundations Marketing for founders who need a professional foundation without a complicated agency package. The result is a clear, functional site with the support to get it live.</p></div></div>
        <div class="fm-story__values"><div>No guesswork</div><div>No endless revisions at midnight</div><div>No contracts that trap you</div></div>
      </section>

      <section class="fm-story__section">
        <div class="fm-story__section-label"><span>04 — Our mission</span><span>Why we do this ↓</span></div>
        <div class="fm-story__split"><h2>You don't have to do this <span>alone</span></h2><div class="fm-story__copy"><p>We exist to give new and growing entrepreneurs, especially those starting fresh chapters, a real marketing foundation without the overwhelm or the agency price tag.</p><p>Purpose-led practitioners deserve clarity and confidence, not another system to figure out on their own. Through simple services and honest support, we remove the guesswork and reduce the stress.</p><p>Together, we're building more than websites. We're building foundations for businesses that work and lives that feel worth it.</p></div></div>
        <img class="fm-story__wide-photo" src="<?php echo esc_url($photo); ?>" alt="Chia and Ralu together">
      </section>

      <section class="fm-story__section">
        <div class="fm-story__section-label"><span>05 — Giving back</span><span>Our connection to Bali ↓</span></div>
        <div class="fm-story__split"><h2>Part of something bigger than a website</h2><div class="fm-story__copy"><p>Our connection to Bali runs deep. Part of what drives us is giving back to the communities that inspire us. With every client we work with, we support local charities in Bali providing resources for parents and children with disabilities.</p><p class="fm-story__accent">One foundation at a time.</p></div></div>
      </section>

      <aside class="fm-story__cta"><h2>Let's build your <span>foundation</span></h2><div><?php echo fm_marketing_cta('See the templates →', '/templates/#templates'); ?><?php echo fm_marketing_cta('Book a free 20-min chat', '/contact-page/', false); ?></div><p>From £299 · Live in 5 days · No contracts · Yours to keep</p></aside>
    </article>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('fm_about_page', 'fm_about_page_shortcode');

function fm_faq_page_shortcode(): string
{
    $faqs = [
        ['What makes you different from other website providers?', '<p>We built Foundations Marketing for the business we wished we had when we started. We are a couple of mums who know firsthand how lonely and frustrating it is to launch a business.</p><p>Our goal is support. We want to tick one huge box off your list—your professional website—and make the process as easy and affordable as possible. You speak to someone who genuinely cares about you succeeding.</p>'],
        ['Why should I choose Foundations Marketing over Wix or Squarespace?', '<p>Because you do not need to do the work yourself. You provide your content and an email address; we turn it into your site.</p><p><strong>Your content:</strong> your text, high-quality photos and service descriptions, submitted through our questionnaire.</p><p><strong>Email address:</strong> an address to connect to your calendar and receive customer queries.</p>'],
        ['If you just provide the template, what else do I need to make my website live?', '<p>Your package includes the design work and implementation. To make it publicly visible, you also need:</p><ul><li><strong>A domain name:</strong> your website address.</li><li><strong>Website hosting:</strong> the server where your website files live.</li></ul><p>We handle the technical setup to connect everything once you have those two things.</p>'],
        ['What other things do I need to get my website going?', '<p>Our sites provide a flexible foundation without unnecessary features or rigid builder lock-in.</p><ul><li><strong>No unnecessary features:</strong> you get what a solopreneur needs to launch and thrive.</li><li><strong>Built to grow:</strong> your site can expand as your business evolves.</li><li><strong>Personal support:</strong> real people support your journey.</li></ul>'],
        ['What if I want to upgrade my website later and add more features?', '<p>You can. We build on a flexible platform, so we can add pages, features or integrations as you grow without forcing a complete rebuild.</p>'],
        ['What can I use as my booking system?', '<p>Our templates can integrate with third-party systems such as Acuity, Calendly or Square Appointments. If you prefer to speak with a potential client first, we can use enquiry forms instead.</p>'],
        ["What if I don't have all the information ready?", '<p>Our turnaround starts once all information is received, so we recommend preparing your content first. Your purchase includes a free 30-minute consultation to review your content needs. We also offer help with branding and logos.</p>'],
        ['When do you start making the website?', '<p>The clock starts when we receive your order confirmation and your completed questionnaire with all necessary text and images.</p>'],
        ['How long does it take to make a website?', '<p>Turnaround depends on your package:</p><div class="fm-faq-page__times"><div><strong>Root package</strong><span>3 working days</span></div><div><strong>Grow package</strong><span>5 working days</span></div><div><strong>Rise package</strong><span>7 working days</span></div></div>'],
        ['My website needs are a little more complex. Do you do custom-built websites?', '<p>Yes. Our sister brand, <a href="https://cularcreative.com/">Cular Creative</a>, handles custom websites and larger projects. Fill out our questionnaire and mention that you need a custom solution.</p>'],
        ['Do you offer website maintenance or support after the website is live?', '<p>Yes. We offer separate monthly maintenance plans to keep your site updated, secure and running smoothly.</p>'],
        ['What training or tutorials do you provide so I can manage my website?', '<p>Every package includes video tutorials showing you how to edit text, swap images and manage the basic functions of your new site.</p>'],
        ['What are the downloadables for, and do I need them?', '<p>The guides, checklists and planning templates help with content, social media and service planning. You do not need them to start your website, but they can make preparation easier.</p>'],
        ['What does a round of revisions mean?', '<p>A revision round is one combined set of feedback after we upload your content into your chosen template.</p><p>It covers small refinements such as:</p><ul><li>Wording or copy changes</li><li>Image swaps</li><li>Minor layout or formatting changes within the template</li></ul><p>It does not cover new pages, structural changes or switching templates. Additional revisions cost £75 per round.</p>'],
    ];

    ob_start();
    ?>
    <article class="fm-story fm-faq-page">
      <header class="fm-story__hero"><div class="fm-story__label"><span>FAQ</span><span>Before you start</span></div><h1>Frequently asked<br><span>questions.</span></h1><div class="fm-story__intro"><h2>Everything we get asked, answered up front.</h2><div><p>If your question isn't here, book a free 20-minute call and ask us directly. You'll speak to one of us, not a help centre.</p><?php echo fm_marketing_cta('Book a discovery call →', '/contact-page/', false); ?></div></div></header>
      <div class="fm-story__ticker" aria-hidden="true"><span>No contracts — no lock-in — real people — mates' rates — no contracts — no lock-in — real people — mates' rates —</span></div>
      <section class="fm-story__section" id="faqs"><div class="fm-story__section-label"><span>01 — The questions</span><span>Tap to open ↓</span></div><div class="fm-faq-page__list">
        <?php foreach ($faqs as $index => [$question, $answer]) : ?>
          <details name="fm-faq"><summary><span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span><strong><?php echo esc_html($question); ?></strong><i aria-hidden="true">+</i></summary><div class="fm-faq-page__answer"><?php echo wp_kses_post($answer); ?></div></details>
        <?php endforeach; ?>
      </div></section>
      <aside class="fm-story__cta"><h2>Still have a <span>question?</span></h2><p>Book a free 20-minute call. No pitch, no pressure—just two people who have been where you are.</p><div><?php echo fm_marketing_cta('Book a discovery call →', '/contact-page/'); ?><?php echo fm_marketing_cta('See the templates', '/templates/#templates', false); ?></div></aside>
    </article>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('fm_faq_page', 'fm_faq_page_shortcode');

function fm_install_marketing_pages(): void
{
    $version = '7';
    if ((string) get_option('fm_native_marketing_pages_version', '') === $version) {
        return;
    }

    $pages = [
        'who-are-we' => ['Who are we', '[fm_about_page]'],
        'frequently-asked-questions' => ['Frequently Asked Questions', '[fm_faq_page]'],
    ];
    foreach ($pages as $slug => [$title, $content]) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        $data = ['post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content];
        if ($page instanceof WP_Post) {
            fm_marketing_update_post($page->ID, $data);
            foreach (['_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_wp_page_template'] as $key) {
                delete_post_meta($page->ID, $key);
            }
        } else {
            wp_insert_post($data);
        }
    }

    foreach (['admin_email', 'new_admin_email', 'fmcd_cc_emails', 'woocommerce_stock_email_recipient', 'woocommerce_pos_store_email'] as $option) {
        if (strtolower(trim((string) get_option($option, ''))) === 'it@cularcreative.com') {
            update_option($option, 'info@foundationsmarketing.co.uk');
        }
    }

    // Apply the agreed price and wording changes to existing block pages. Defaults
    // only affect newly inserted blocks, while these pages already store attributes.
    foreach (['fm-block-test', 'services', 'templates', 'build-your-site'] as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!$page instanceof WP_Post) {
            continue;
        }
        $content = str_replace(
            ['£249', '\u00a3249', '"basePrice":249', 'Custom copy, colors, logo, and images', 'Custom copy, colours, logo and images'],
            ['£299', '\u00a3299', '"basePrice":299', 'Your copy, colours, logo and images applied', 'Your copy, colours, logo and images applied'],
            $page->post_content
        );
        if ($slug === 'build-your-site') {
            $content = preg_replace('/("id":"booking"[^}]*"price":)75/', '${1}45', $content) ?: $content;
        } elseif ($slug === 'fm-block-test') {
            $content = preg_replace('/("name":"Booking integration","price":")£75/', '${1}£45', $content) ?: $content;
        } elseif ($slug === 'services') {
            $content = preg_replace('/("name":"Booking integration","price":")£75/', '${1}£45', $content) ?: $content;
        }
        if ($content !== $page->post_content) {
            fm_marketing_update_post($page->ID, ['post_content' => $content]);
        }
    }

    // The Root product is the base template purchase used by the classic checkout.
    $root = get_page_by_path('root', OBJECT, 'product');
    if ($root instanceof WP_Post && function_exists('wc_get_product')) {
        $product = wc_get_product($root->ID);
        if ($product && (float) $product->get_regular_price() === 249.0) {
            $product->set_regular_price('299');
            $product->set_price('299');
            $product->save();
        }
    }

    if ((string) get_option('blogname', '') === 'Foundation Marketing') {
        update_option('blogname', 'Foundations Marketing');
    }

    // Dev predates the plural catalogue URL. Rename the existing page in place so
    // its content, ID, menu relationships and SEO history are retained.
    foreach (['template' => ['templates', 'Templates'], 'service' => ['services', 'Services']] as $old_slug => [$new_slug, $title]) {
        $old_page = get_page_by_path($old_slug, OBJECT, 'page');
        $new_page = get_page_by_path($new_slug, OBJECT, 'page');
        if ($old_page instanceof WP_Post && !$new_page instanceof WP_Post) {
            fm_marketing_update_post($old_page->ID, ['post_name' => $new_slug, 'post_title' => $title]);
        }
    }

    // Run after the singular-page rename: on older installs the catalogue was not at
    // /templates/ when the first content pass above ran.
    foreach (['templates' => 'Templates', 'services' => 'Services'] as $slug => $title) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if (!$page instanceof WP_Post) {
            continue;
        }
        $content = str_replace(['£249', '\u00a3249'], ['£299', '\u00a3299'], $page->post_content);
        if ($slug === 'services') {
            $content = preg_replace('/("name":"Booking integration","price":")£75/', '${1}£45', $content) ?: $content;
        }
        fm_marketing_update_post($page->ID, ['post_title' => $title, 'post_content' => $content]);
    }

    // Remove the retired Pricing item from both current menus and normalise the two
    // catalogue page links. Old Elementor menus are unassigned and intentionally left.
    foreach (['primary', 'footer'] as $location) {
        $locations = get_nav_menu_locations();
        foreach ((array) wp_get_nav_menu_items($locations[$location] ?? 0) as $item) {
            $label = strtolower(trim(wp_strip_all_tags((string) $item->title)));
            if ($label === 'pricing') {
                wp_delete_post($item->ID, true);
                continue;
            }
            if ($label === 'template' || $label === 'templates') {
                update_post_meta($item->ID, '_menu_item_url', '/templates/');
                fm_marketing_update_post($item->ID, ['post_title' => 'Templates']);
            }
            if ($label === 'service' || $label === 'services') {
                update_post_meta($item->ID, '_menu_item_url', '/services/');
                fm_marketing_update_post($item->ID, ['post_title' => 'Services']);
            }
        }
    }

    update_option('fm_native_marketing_pages_version', $version, false);
}
add_action('init', 'fm_install_marketing_pages', 31);

/** Preserve old inbound links while keeping the canonical page URLs plural. */
function fm_redirect_singular_marketing_urls(): void
{
    $path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $base = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
    if ($base !== '' && str_starts_with($path, $base . '/')) {
        $path = substr($path, strlen($base) + 1);
    }
    if ($path === 'template' || $path === 'service') {
        wp_safe_redirect(home_url('/' . $path . 's/'), 301);
        exit;
    }
}
add_action('template_redirect', 'fm_redirect_singular_marketing_urls', 1);
