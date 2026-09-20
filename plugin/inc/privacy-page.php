<?php
/** Native marketing-site privacy page and one-time migration from the legacy page. */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function fm_privacy_page_shortcode(): string
{
    $sections = [
        'introduction' => ['Introduction', '<p>Welcome to Foundations Marketing. We are committed to protecting your privacy. This policy details the types of personal information we collect, how it is used, and the measures we take to ensure your personal data is handled appropriately.</p>'],
        'who-we-are' => ['Who we are', '<p>Foundations Marketing is a UK-based business. Our website address is <a href="https://foundationsmarketing.co.uk/">foundationsmarketing.co.uk</a>. We provide website templates, website builds and related digital services and products.</p>'],
        'data-we-collect' => ['Personal data we collect', '<p>We may collect personal data by telephone, post, email, through our website, or in person. The information may include:</p><ul><li>Names and contact details</li><li>Addresses and dates of birth</li><li>Purchase or account history</li><li>Payment records for transfers and direct debits</li><li>Website usage, user journeys and cookie information</li><li>Records of meetings and decisions</li><li>Compliments, complaints and support requests</li></ul>'],
        'why-we-collect-it' => ['Why we collect it', '<p>We use this information to:</p><ul><li>Provide and deliver our services and products</li><li>Operate customer accounts and guarantees</li><li>Send service updates or marketing communications where permitted</li><li>Respond to enquiries and provide support</li><li>Comply with legal and accounting requirements</li></ul>'],
        'processors' => ['Service providers', '<p>We use trusted third-party providers to help operate our business. They may process personal data on our behalf and are required to protect it. These services include Gmail for email communication, WhatsApp for instant messaging, website hosting, payment processing and the systems used to manage enquiries and projects.</p>'],
        'sharing' => ['How we share data', '<p>We do not sell your personal data. We may share it with service providers working on our behalf, professional advisers, or public authorities where the law requires it. Information is only published on our website, social media or marketing channels where we have an appropriate reason or permission to do so.</p>'],
        'security' => ['Data security', '<p>We use appropriate technical and organisational measures to protect personal data against unauthorised or unlawful access, alteration, disclosure, loss or destruction.</p>'],
        'rights' => ['Your rights', '<p>Under the UK GDPR, you may have the right to access, correct, delete or restrict the processing of your personal data, object to certain processing, and withdraw consent where consent is the legal basis. To exercise your rights, email <a href="mailto:info@foundationsmarketing.co.uk">info@foundationsmarketing.co.uk</a>.</p>'],
        'changes' => ['Changes to this policy', '<p>We may update this privacy policy from time to time. Significant changes will be published on this page or communicated by email where appropriate.</p>'],
        'contact' => ['Contact us', '<p>If you have questions about this policy or how we handle your personal data, email <a href="mailto:info@foundationsmarketing.co.uk">info@foundationsmarketing.co.uk</a>.</p>'],
    ];

    ob_start();
    ?>
    <article class="fm-legal">
      <header class="fm-legal__hero">
        <div class="fm-legal__eyebrow"><span>Legal</span><span>Privacy &amp; data</span></div>
        <h1>Privacy<br><span>policy.</span></h1>
        <div class="fm-legal__intro">
          <p>How Foundations Marketing collects, uses and protects the information you share with us.</p>
          <p class="fm-legal__date">Effective from 2025<br>Reviewed 20 September 2026</p>
        </div>
      </header>

      <div class="fm-legal__body">
        <nav class="fm-legal__contents" aria-label="Privacy policy contents">
          <p>On this page</p>
          <ol>
            <?php foreach ($sections as $id => [$heading]) : ?>
              <li><a href="#<?php echo esc_attr($id); ?>"><?php echo esc_html($heading); ?></a></li>
            <?php endforeach; ?>
          </ol>
        </nav>

        <div class="fm-legal__sections">
          <?php $number = 1; foreach ($sections as $id => [$heading, $content]) : ?>
            <section id="<?php echo esc_attr($id); ?>" class="fm-legal__section">
              <span class="fm-legal__number"><?php echo esc_html(str_pad((string) $number++, 2, '0', STR_PAD_LEFT)); ?></span>
              <div><h2><?php echo esc_html($heading); ?></h2><?php echo wp_kses_post($content); ?></div>
            </section>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="fm-legal__contact">
        <p>Questions about your data?</p>
        <a href="mailto:info@foundationsmarketing.co.uk">info@foundationsmarketing.co.uk <span aria-hidden="true">&rarr;</span></a>
      </aside>
    </article>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('fm_privacy_page', 'fm_privacy_page_shortcode');

function fm_install_privacy_page(): void
{
    $version = '1';
    if ((string) get_option('fm_native_privacy_version', '') === $version) {
        return;
    }

    $page = get_page_by_path('privacy-policy', OBJECT, 'page');
    $data = [
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_name' => 'privacy-policy',
        'post_title' => 'Privacy Policy',
        'post_content' => '[fm_privacy_page]',
    ];

    if ($page instanceof WP_Post) {
        $data['ID'] = $page->ID;
        wp_update_post($data);
        foreach (['_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_wp_page_template'] as $key) {
            delete_post_meta($page->ID, $key);
        }
    } else {
        wp_insert_post($data);
    }

    update_option('fm_native_privacy_version', $version, false);
}
add_action('init', 'fm_install_privacy_page', 30);
