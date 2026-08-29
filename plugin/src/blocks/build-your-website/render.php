<?php
/**
 * Build Your Website block.
 *
 * @var array $attributes
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$topbar_text = (string) ($attributes['topbarText'] ?? 'Your new website starts here — choose a direction and make it yours.');
$logo_text = (string) ($attributes['logoText'] ?? 'Foundations.');
$nav_links = [
    ['label' => (string) ($attributes['navLinkOne'] ?? 'Templates'), 'url' => (string) ($attributes['navLinkOneUrl'] ?? '#templates')],
    ['label' => (string) ($attributes['navLinkTwo'] ?? "What's Included"), 'url' => (string) ($attributes['navLinkTwoUrl'] ?? '#features')],
    ['label' => (string) ($attributes['navLinkThree'] ?? 'Support'), 'url' => (string) ($attributes['navLinkThreeUrl'] ?? '#support')],
];
$primary_button_text = (string) ($attributes['primaryButtonText'] ?? 'Build My Site →');
$primary_button_url = (string) ($attributes['primaryButtonUrl'] ?? '#templates');
$eyebrow = (string) ($attributes['eyebrow'] ?? 'Build your website');
$heading = (string) ($attributes['heading'] ?? 'Your business deserves a site that');
$heading_accent = (string) ($attributes['headingAccent'] ?? 'feels like you.');
$lede = (string) ($attributes['lede'] ?? 'Pick a starting point, customize your content and turn your ideas into a website you\'re actually proud to share.');
$hero_primary_text = (string) ($attributes['heroPrimaryText'] ?? 'Choose your template');
$hero_primary_url = (string) ($attributes['heroPrimaryUrl'] ?? '#templates');
$hero_secondary_text = (string) ($attributes['heroSecondaryText'] ?? "See what's included");
$hero_secondary_url = (string) ($attributes['heroSecondaryUrl'] ?? '#features');
$journey_title = (string) ($attributes['journeyTitle'] ?? 'Your journey');
$journey_steps = [
    (string) ($attributes['journeyStepOne'] ?? 'Choose style'),
    (string) ($attributes['journeyStepTwo'] ?? 'Add content'),
    (string) ($attributes['journeyStepThree'] ?? 'Customize'),
    (string) ($attributes['journeyStepFour'] ?? 'Launch'),
];
$section_heading = (string) ($attributes['sectionHeading'] ?? 'Start with a look that speaks your language.');
$section_intro = (string) ($attributes['sectionIntro'] ?? 'These layouts are designed as flexible starting points. Choose the direction closest to your brand.');
$features_title = (string) ($attributes['featuresTitle'] ?? 'More than just a pretty homepage.');
$features_intro = (string) ($attributes['featuresIntro'] ?? 'Everything you need');
$cta_heading = (string) ($attributes['ctaHeading'] ?? 'Ready to make your corner of the internet?');
$cta_text = (string) ($attributes['ctaText'] ?? "Choose your starting point and we'll take it from there.");
$cta_button_text = (string) ($attributes['ctaButtonText'] ?? 'Start building →');
$cta_button_url = (string) ($attributes['ctaButtonUrl'] ?? '#templates');
$footer_logo = (string) ($attributes['footerLogo'] ?? 'Foundations.');
$footer_copy = (string) ($attributes['footerCopy'] ?? '© 2026 Foundations Marketing');
?>
<section <?php echo fm_wrapper(['fm-build-your-website']); ?>>
    <div class="fm-build-your-website__topbar"><?php echo esc_html($topbar_text); ?></div>

    <nav class="fm-build-your-website__nav" aria-label="Primary navigation">
        <div class="fm-build-your-website__inner">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="fm-build-your-website__logo"><?php echo esc_html($logo_text); ?></a>

            <ul class="fm-build-your-website__links">
                <?php foreach ($nav_links as $nav_item) : ?>
                    <li>
                        <a href="<?php echo esc_url((string) $nav_item['url']); ?>"><?php echo esc_html((string) $nav_item['label']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <a href="<?php echo esc_url($primary_button_url); ?>" class="fm-build-your-website__button"><?php echo esc_html($primary_button_text); ?></a>
        </div>
    </nav>

    <main class="fm-build-your-website__main">
        <section class="fm-build-your-website__hero">
            <div class="fm-build-your-website__inner">
                <div class="fm-build-your-website__copy">
                    <div class="fm-build-your-website__eyebrow"><?php echo esc_html($eyebrow); ?></div>
                    <h1 class="fm-build-your-website__title">
                        <?php echo esc_html($heading); ?>
                        <em><?php echo esc_html($heading_accent); ?></em>
                    </h1>
                    <p class="fm-build-your-website__lede"><?php echo esc_html($lede); ?></p>
                    <div class="fm-build-your-website__actions">
                        <a href="<?php echo esc_url($hero_primary_url); ?>" class="fm-build-your-website__cta"><?php echo esc_html($hero_primary_text); ?></a>
                        <a href="<?php echo esc_url($hero_secondary_url); ?>" class="fm-build-your-website__link"><?php echo esc_html($hero_secondary_text); ?></a>
                    </div>
                </div>

                <div class="fm-build-your-website__visual" aria-hidden="true">
                    <div class="fm-build-your-website__orange-shape"></div>
                    <div class="fm-build-your-website__yellow-block"></div>
                    <div class="fm-build-your-website__browser-card">
                        <div class="fm-build-your-website__browser-bar">
                            <span class="fm-build-your-website__browser-dot"></span>
                            <span class="fm-build-your-website__browser-dot"></span>
                            <span class="fm-build-your-website__browser-dot"></span>
                        </div>
                        <div class="fm-build-your-website__browser-content">
                            <div class="fm-build-your-website__fake-nav"></div>
                            <div class="fm-build-your-website__fake-title"></div>
                            <div class="fm-build-your-website__fake-title fm-build-your-website__fake-title--small"></div>
                            <div class="fm-build-your-website__fake-text">
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                            <div class="fm-build-your-website__fake-button"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="fm-build-your-website__journey">
            <div class="fm-build-your-website__journey-card">
                <div class="fm-build-your-website__journey-label"><?php echo esc_html($journey_title); ?></div>
                <div class="fm-build-your-website__steps">
                    <?php foreach ($journey_steps as $index => $step) : ?>
                        <div class="fm-build-your-website__step <?php echo $index === 0 ? 'is-active' : ''; ?>">
                            <div class="fm-build-your-website__step-number"><?php echo (int) ($index + 1); ?></div>
                            <span><?php echo esc_html($step); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="fm-build-your-website__progress-status">Step 1 of 4</div>
            </div>
        </section>

        <section class="fm-build-your-website__templates" id="templates">
            <div class="fm-build-your-website__section-head">
                <h2><?php echo esc_html($section_heading); ?></h2>
                <p><?php echo esc_html($section_intro); ?></p>
            </div>

            <div class="fm-build-your-website__template-grid">
                <article class="fm-build-your-website__template-card">
                    <div class="fm-build-your-website__template-preview fm-build-your-website__template-preview--one">
                        <div class="fm-build-your-website__mini-browser">
                            <div class="fm-build-your-website__mini-nav"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-image"></div>
                        </div>
                    </div>
                    <div class="fm-build-your-website__template-info">
                        <div class="fm-build-your-website__template-number">TEMPLATE 01</div>
                        <div class="fm-build-your-website__template-title-row">
                            <h3>Warm & Human</h3>
                            <span aria-hidden="true">↗</span>
                        </div>
                        <p>Soft colors, editorial typography and plenty of breathing room for personal brands.</p>
                    </div>
                </article>

                <article class="fm-build-your-website__template-card">
                    <div class="fm-build-your-website__template-preview fm-build-your-website__template-preview--two">
                        <div class="fm-build-your-website__mini-browser">
                            <div class="fm-build-your-website__mini-nav"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-image fm-build-your-website__mini-image--dark"></div>
                        </div>
                    </div>
                    <div class="fm-build-your-website__template-info">
                        <div class="fm-build-your-website__template-number">TEMPLATE 02</div>
                        <div class="fm-build-your-website__template-title-row">
                            <h3>Bold Studio</h3>
                            <span aria-hidden="true">↗</span>
                        </div>
                        <p>High contrast layouts designed for creatives, consultants and ambitious service brands.</p>
                    </div>
                </article>

                <article class="fm-build-your-website__template-card">
                    <div class="fm-build-your-website__template-preview fm-build-your-website__template-preview--three">
                        <div class="fm-build-your-website__mini-browser">
                            <div class="fm-build-your-website__mini-nav"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-heading"></div>
                            <div class="fm-build-your-website__mini-image fm-build-your-website__mini-image--warm"></div>
                        </div>
                    </div>
                    <div class="fm-build-your-website__template-info">
                        <div class="fm-build-your-website__template-number">TEMPLATE 03</div>
                        <div class="fm-build-your-website__template-title-row">
                            <h3>Natural Flow</h3>
                            <span aria-hidden="true">↗</span>
                        </div>
                        <p>Calm, natural and easygoing design for wellness, lifestyle and purpose-led businesses.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="fm-build-your-website__features" id="features">
            <div class="fm-build-your-website__features-grid">
                <div class="fm-build-your-website__features-title">
                    <span><?php echo esc_html($features_intro); ?></span>
                    <h2><?php echo esc_html($features_title); ?></h2>
                </div>

                <div class="fm-build-your-website__feature-list">
                    <div class="fm-build-your-website__feature-item">
                        <div class="fm-build-your-website__feature-icon">01</div>
                        <div>
                            <h3>Responsive by default</h3>
                            <p>Your site adapts beautifully across desktop, tablet and mobile without creating separate layouts.</p>
                        </div>
                    </div>
                    <div class="fm-build-your-website__feature-item">
                        <div class="fm-build-your-website__feature-icon">02</div>
                        <div>
                            <h3>Flexible page sections</h3>
                            <p>Rearrange sections, change content and make each page work around the needs of your business.</p>
                        </div>
                    </div>
                    <div class="fm-build-your-website__feature-item">
                        <div class="fm-build-your-website__feature-icon">03</div>
                        <div>
                            <h3>Built for speed</h3>
                            <p>Clean structure and lightweight styling provide a solid foundation for a fast website.</p>
                        </div>
                    </div>
                    <div class="fm-build-your-website__feature-item">
                        <div class="fm-build-your-website__feature-icon">04</div>
                        <div>
                            <h3>Easy to customize</h3>
                            <p>Update typography, colors, imagery and content while maintaining a consistent visual system.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="fm-build-your-website__cta-section" id="cta">
            <div class="fm-build-your-website__cta-inner">
                <h2><?php echo esc_html($cta_heading); ?></h2>
                <div class="fm-build-your-website__cta-right">
                    <p><?php echo esc_html($cta_text); ?></p>
                    <a href="<?php echo esc_url($cta_button_url); ?>" class="fm-build-your-website__cta-button"><?php echo esc_html($cta_button_text); ?></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="fm-build-your-website__footer">
        <div class="fm-build-your-website__footer-inner">
            <div class="fm-build-your-website__footer-logo"><?php echo esc_html($footer_logo); ?></div>
            <div class="fm-build-your-website__footer-copy"><?php echo esc_html($footer_copy); ?></div>
        </div>
    </footer>
</section>
