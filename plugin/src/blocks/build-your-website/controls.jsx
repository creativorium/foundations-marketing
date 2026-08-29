/**
 * Controls for foundations/build-your-website.
 */
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Controls({ attributes, setAttributes }) {
  const {
    topbarText,
    logoText,
    navLinkOne,
    navLinkTwo,
    navLinkThree,
    primaryButtonText,
    primaryButtonUrl,
    eyebrow,
    heading,
    headingAccent,
    lede,
    heroPrimaryText,
    heroPrimaryUrl,
    heroSecondaryText,
    heroSecondaryUrl,
    journeyTitle,
    sectionHeading,
    sectionIntro,
    featuresTitle,
    featuresIntro,
    ctaHeading,
    ctaText,
    ctaButtonText,
    ctaButtonUrl,
    footerLogo,
    footerCopy,
  } = attributes;

  return (
    <>
      <PanelBody title={__('Top bar', 'foundations')} initialOpen>
        <TextControl label={__('Top bar text', 'foundations')} value={topbarText} onChange={(v) => setAttributes({ topbarText: v })} />
        <TextControl label={__('Logo text', 'foundations')} value={logoText} onChange={(v) => setAttributes({ logoText: v })} />
      </PanelBody>

      <PanelBody title={__('Navigation', 'foundations')}>
        <TextControl label={__('Nav item 1', 'foundations')} value={navLinkOne} onChange={(v) => setAttributes({ navLinkOne: v })} />
        <TextControl label={__('Nav item 2', 'foundations')} value={navLinkTwo} onChange={(v) => setAttributes({ navLinkTwo: v })} />
        <TextControl label={__('Nav item 3', 'foundations')} value={navLinkThree} onChange={(v) => setAttributes({ navLinkThree: v })} />
        <TextControl label={__('Primary CTA text', 'foundations')} value={primaryButtonText} onChange={(v) => setAttributes({ primaryButtonText: v })} />
        <TextControl label={__('Primary CTA URL', 'foundations')} value={primaryButtonUrl} onChange={(v) => setAttributes({ primaryButtonUrl: v })} />
      </PanelBody>

      <PanelBody title={__('Hero', 'foundations')}>
        <TextControl label={__('Eyebrow', 'foundations')} value={eyebrow} onChange={(v) => setAttributes({ eyebrow: v })} />
        <TextControl label={__('Heading', 'foundations')} value={heading} onChange={(v) => setAttributes({ heading: v })} />
        <TextControl label={__('Heading accent', 'foundations')} value={headingAccent} onChange={(v) => setAttributes({ headingAccent: v })} />
        <TextControl label={__('Hero lede', 'foundations')} value={lede} onChange={(v) => setAttributes({ lede: v })} />
        <TextControl label={__('Primary hero button', 'foundations')} value={heroPrimaryText} onChange={(v) => setAttributes({ heroPrimaryText: v })} />
        <TextControl label={__('Primary hero URL', 'foundations')} value={heroPrimaryUrl} onChange={(v) => setAttributes({ heroPrimaryUrl: v })} />
        <TextControl label={__('Secondary hero button', 'foundations')} value={heroSecondaryText} onChange={(v) => setAttributes({ heroSecondaryText: v })} />
        <TextControl label={__('Secondary hero URL', 'foundations')} value={heroSecondaryUrl} onChange={(v) => setAttributes({ heroSecondaryUrl: v })} />
      </PanelBody>

      <PanelBody title={__('Journey + content', 'foundations')}>
        <TextControl label={__('Journey title', 'foundations')} value={journeyTitle} onChange={(v) => setAttributes({ journeyTitle: v })} />
        <TextControl label={__('Section heading', 'foundations')} value={sectionHeading} onChange={(v) => setAttributes({ sectionHeading: v })} />
        <TextControl label={__('Section intro', 'foundations')} value={sectionIntro} onChange={(v) => setAttributes({ sectionIntro: v })} />
        <TextControl label={__('Features title', 'foundations')} value={featuresTitle} onChange={(v) => setAttributes({ featuresTitle: v })} />
        <TextControl label={__('Features intro label', 'foundations')} value={featuresIntro} onChange={(v) => setAttributes({ featuresIntro: v })} />
      </PanelBody>

      <PanelBody title={__('CTA + footer', 'foundations')}>
        <TextControl label={__('CTA heading', 'foundations')} value={ctaHeading} onChange={(v) => setAttributes({ ctaHeading: v })} />
        <TextControl label={__('CTA text', 'foundations')} value={ctaText} onChange={(v) => setAttributes({ ctaText: v })} />
        <TextControl label={__('CTA button label', 'foundations')} value={ctaButtonText} onChange={(v) => setAttributes({ ctaButtonText: v })} />
        <TextControl label={__('CTA button URL', 'foundations')} value={ctaButtonUrl} onChange={(v) => setAttributes({ ctaButtonUrl: v })} />
        <TextControl label={__('Footer logo', 'foundations')} value={footerLogo} onChange={(v) => setAttributes({ footerLogo: v })} />
        <TextControl label={__('Footer copy', 'foundations')} value={footerCopy} onChange={(v) => setAttributes({ footerCopy: v })} />
      </PanelBody>
    </>
  );
}
