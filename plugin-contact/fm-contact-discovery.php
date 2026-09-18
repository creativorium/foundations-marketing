<?php
/**
 * Plugin Name: FM Contact & Discovery Forms (with Appointment)
 * Description: Custom Form for Foundations Marketing. Shortcodes: [fm_contact_form], [fm_discovery_form]
 * Version: 1.9.4
 * Author: Negolast
 */

if (!defined('ABSPATH')) exit;

define('FMCD_VER', '1.9.4');
define('FMCD_DIR', plugin_dir_path(__FILE__));
define('FMCD_URL', plugin_dir_url(__FILE__));

require_once FMCD_DIR . 'includes/class-fm-admin.php';
require_once FMCD_DIR . 'includes/class-fm-mail.php';
require_once FMCD_DIR . 'includes/class-fm-cpt.php';
require_once FMCD_DIR . 'includes/class-fm-integrations.php'; // NEW/UPDATED
require_once FMCD_DIR . 'includes/class-fm-rest.php';

class FMCD_Plugin {
  public function __construct() {
    add_action('init', [$this, 'init']);
    add_action('wp_enqueue_scripts', [$this, 'assets']);
    add_shortcode('fm_contact_form', [$this, 'shortcode_contact']);
    add_shortcode('fm_contact_page', [$this, 'shortcode_contact_page']);
    add_shortcode('fm_discovery_form', [$this, 'shortcode_discovery']);
    add_shortcode('fm_discovery_page', [$this, 'shortcode_discovery_page']);
    new FMCD_Admin;
    new FMCD_CPT;
    new FMCD_REST;
    add_action('fmcd_mailchimp_sync', [$this, 'handle_mailchimp_sync'], 10, 2);
  }

  public function init() {
    $this->maybe_install_native_form_pages();
  }

  private function maybe_install_native_form_pages() {
    $native_pages_version = '2';
    if ((string) get_option('fmcd_native_pages_version', '') === $native_pages_version) return;

    $pages = [
      'contact-page' => ['title' => 'Contact', 'shortcode' => '[fm_contact_page]', 'legacy' => '[fm_contact_form]'],
      'foundation-website-discovery-form' => ['title' => 'Foundation Website Discovery Form', 'shortcode' => '[fm_discovery_page]', 'legacy' => '[fm_discovery_form]'],
    ];

    foreach ($pages as $slug => $config) {
      $page = get_page_by_path($slug, OBJECT, 'page');

      if (!$page instanceof WP_Post) {
        wp_insert_post([
          'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
          'post_title' => $config['title'], 'post_content' => $config['shortcode'],
        ]);
        continue;
      }

      $uses_elementor = get_post_meta($page->ID, '_elementor_edit_mode', true) === 'builder';
      $uses_legacy_shortcode = str_contains((string) $page->post_content, $config['legacy']);
      if (!$uses_elementor && !$uses_legacy_shortcode && !str_contains((string) $page->post_content, $config['shortcode'])) continue;

      wp_update_post([
        'ID' => $page->ID,
        'post_content' => $config['shortcode'],
        'post_status' => 'publish',
        'page_template' => 'default',
      ]);
      foreach (['_elementor_data', '_elementor_edit_mode', '_elementor_page_settings', '_elementor_template_type', '_wp_page_template'] as $key) {
        delete_post_meta($page->ID, $key);
      }
    }

    update_option('fmcd_native_pages_version', $native_pages_version, false);
  }

  public function assets() {
  wp_register_style('fmcd-form', FMCD_URL . 'assets/form.css', [], FMCD_VER);
  wp_register_script('fmcd-form', FMCD_URL . 'assets/form.js', ['jquery'], FMCD_VER, true);

  // ⬇️ GUARANTEE the inline step functions exist on every page that loads fmcd-form
  $inline = <<<'JS'
    window.fmddClearFieldError = function(field){
      try{
        if (!field) return;
        var label = field.closest('label');
        if (label){
          label.classList.remove('fmdd-field-error');
          var msg = label.querySelector('.fmdd-error-msg');
          if (msg) msg.classList.remove('visible');
        }
        if (field.setCustomValidity) field.setCustomValidity('');
      }catch(e){ console.error('fmddClearFieldError error', e); }
    };

    window.fmddShowFieldError = function(field, message){
      try{
        if (!field) return;
        var label = field.closest('label');
        if (label){
          label.classList.add('fmdd-field-error');
          var msg = label.querySelector('.fmdd-error-msg');
          if (!msg){
            msg = document.createElement('div');
            msg.className = 'fmdd-error-msg';
            label.appendChild(msg);
          }
          msg.textContent = message || 'Please complete this field.';
          msg.classList.add('visible');
        }
        if (field.setCustomValidity) field.setCustomValidity(message || 'Please complete this field.');
      }catch(e){ console.error('fmddShowFieldError error', e); }
    };

    window.fmddValidateStep = function(form, step){
      try{
        var section = form.querySelector('.fmdd-step[data-step="'+step+'"]');
        if (!section) return true;

        var firstInvalid = null;

        section.querySelectorAll('label.fmdd-field-error').forEach(function(label){
          label.classList.remove('fmdd-field-error');
          var msg = label.querySelector('.fmdd-error-msg');
          if (msg) msg.classList.remove('visible');
          var input = label.querySelector('input, select, textarea');
          if (input && input.setCustomValidity) input.setCustomValidity('');
        });
        section.querySelectorAll('.fmdd-group-error').forEach(function(g){ g.classList.remove('fmdd-group-error'); });
        section.querySelectorAll('[data-required-group] .fmdd-error-msg.visible').forEach(function(msg){ msg.classList.remove('visible'); });
        section.querySelectorAll('[data-required-radio] .fmdd-error-msg.visible').forEach(function(msg){ msg.classList.remove('visible'); });

        var fields = section.querySelectorAll('input, select, textarea');
        fields.forEach(function(field){
          if (!field || !field.tagName) return;
          if (field.disabled) return;
          if (!field.hasAttribute('required')) return;
          if (field.type === 'checkbox' || field.type === 'radio') return; // checkbox groups handled separately
          if (field.closest('.hidden')) return;
          if (field.offsetParent === null) return;
          if (String(field.value || '').trim()){
            window.fmddClearFieldError(field);
            return;
          }
          var msgText = field.getAttribute('data-error') || (field.tagName === 'SELECT' ? 'Please choose an option.' : 'Please complete this field.');
          window.fmddShowFieldError(field, msgText);
          if (!firstInvalid) firstInvalid = field;
        });

        var radioGroups = section.querySelectorAll('[data-required-radio]');
        radioGroups.forEach(function(group){
          var inputs = group.querySelectorAll('input[type="radio"]');
          if (!inputs.length) return;
          var anyChecked = false;
          for (var i=0; i<inputs.length; i++){
            if (inputs[i].checked){
              anyChecked = true;
              break;
            }
          }
          if (!anyChecked){
            group.classList.add('fmdd-group-error');
            var msg = group.querySelector('.fmdd-error-msg');
            if (!msg){
              msg = document.createElement('div');
              msg.className = 'fmdd-error-msg';
              group.appendChild(msg);
            }
            msg.textContent = group.getAttribute('data-required-message') || 'Please choose an option.';
            msg.classList.add('visible');
            for (var j=0; j<inputs.length; j++){
              if (inputs[j].setCustomValidity) inputs[j].setCustomValidity(msg.textContent);
            }
            if (!firstInvalid && inputs[0]) firstInvalid = inputs[0];
          } else {
            group.classList.remove('fmdd-group-error');
            var existing = group.querySelector('.fmdd-error-msg');
            if (existing) existing.classList.remove('visible');
            for (var k=0; k<inputs.length; k++){
              if (inputs[k].setCustomValidity) inputs[k].setCustomValidity('');
            }
          }
        });

        var groups = section.querySelectorAll('[data-required-group]');
        for (var i=0; i<groups.length; i++){
          var group = groups[i];
          var inputs = group.querySelectorAll('input[type="checkbox"]');
          if (!inputs.length) continue;
          var anyChecked = false;
          for (var j=0; j<inputs.length; j++){ if (inputs[j].checked){ anyChecked = true; break; } }
          if (!anyChecked){
            group.classList.add('fmdd-group-error');
            var msg = group.querySelector('.fmdd-error-msg');
            if (!msg){
              msg = document.createElement('div');
              msg.className = 'fmdd-error-msg';
              msg.textContent = group.getAttribute('data-required-message') || 'Please select at least one option.';
              group.appendChild(msg);
            }
            msg.classList.add('visible');
            for (var k=0; k<inputs.length; k++){ if (inputs[k].setCustomValidity) inputs[k].setCustomValidity(msg.textContent); }
            if (!firstInvalid && inputs[0]) firstInvalid = inputs[0];
          } else {
            group.classList.remove('fmdd-group-error');
            var existing = group.querySelector('.fmdd-error-msg');
            if (existing) existing.classList.remove('visible');
            for (var m=0; m<inputs.length; m++){ if (inputs[m].setCustomValidity) inputs[m].setCustomValidity(''); }
          }
        }

        if (firstInvalid){
          if (typeof firstInvalid.reportValidity === 'function') firstInvalid.reportValidity();
          else firstInvalid.focus();
          return false;
        }

        return true;
      }catch(e){ console.error('fmddValidateStep error', e); return true; }
    };

    window.fmddInlineNext = function(el){
      try{
        var form = el.closest('.fmdd-form'); if(!form) return;
        var cur  = parseInt(form.getAttribute('data-fmdd-step')||'1',10) || 1;
        var next = cur + 1;

        if (!window.fmddValidateStep(form, cur)) return;
    
        form.querySelectorAll('.fmdd-step').forEach(function(sec){ sec.classList.add('hidden'); });
        var target = form.querySelector('.fmdd-step[data-step="'+next+'"]');
        if (target) target.classList.remove('hidden');
    
        form.querySelectorAll('.fmdd-steps button').forEach(function(b){ b.classList.remove('active'); });
        var tab = form.querySelector('.fmdd-steps button[data-step="'+next+'"]');
        if (tab) tab.classList.add('active');
    
        form.setAttribute('data-fmdd-step', String(next));
      }catch(e){ console.error('fmddInlineNext error', e); }
    };
    window.fmddInlineBack = function(el){
      try{
        var form = el.closest('.fmdd-form'); if(!form) return;
        var cur  = parseInt(form.getAttribute('data-fmdd-step')||'1',10) || 1;
        var prev = Math.max(1, cur - 1);
    
        form.querySelectorAll('.fmdd-step').forEach(function(sec){ sec.classList.add('hidden'); });
        var target = form.querySelector('.fmdd-step[data-step="'+prev+'"]');
        if (target) target.classList.remove('hidden');
    
        form.querySelectorAll('.fmdd-steps button').forEach(function(b){ b.classList.remove('active'); });
        var tab = form.querySelector('.fmdd-steps button[data-step="'+prev+'"]');
        if (tab) tab.classList.add('active');
    
        form.setAttribute('data-fmdd-step', String(prev));
      }catch(e){ console.error('fmddInlineBack error', e); }
    };
    
    // Inline availability fetch + render (fallback if main JS is blocked)
    window.fmcdInlineFetchSlots = function(form){
      try{
        var box = form && form.querySelector('.fmcd-appointment'); if(!box) return;
        var dateEl = box.querySelector('.fmcd-date'); if(!dateEl || !dateEl.value) return;
        var date = dateEl.value;
        var slotsWrap = box.querySelector('.fmcd-slots'); var iso = form.querySelector('[name="appt_iso"]');
        var note = box.querySelector('.fmcd-note'); if (note) note.textContent = 'Loading times…';
        if (slotsWrap) slotsWrap.innerHTML = '';
        function render(slots){
          if (!slotsWrap) return; slotsWrap.innerHTML = '';
          if (!slots || !slots.length){ slotsWrap.innerHTML = '<em>No times available for this date.</em>'; if (note) note.textContent=''; return; }
          slots.forEach(function(s){
            var st = new Date(s.start);
            var label = st.toLocaleTimeString('en-GB', {hour:'2-digit', minute:'2-digit', timeZone:'Europe/London'});
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'fmcd-slot'; btn.textContent = label;
            if (!s.available){ btn.disabled = true; btn.classList.add('is-disabled'); btn.title = 'Unavailable'; }
            btn.addEventListener('click', function(){
              Array.prototype.forEach.call(slotsWrap.querySelectorAll('.fmcd-slot'), function(b){ b.classList.remove('selected'); });
              btn.classList.add('selected');
              if (iso) iso.value = s.start; form && form.setAttribute('data-slot-end', s.end || '');
            });
            slotsWrap.appendChild(btn);
          });
          if (note) note.textContent = 'Select a time:';
        }
        function fallback(){
          var ep = (window.FMCD && FMCD.calendar && FMCD.calendar.endpoint) || '';
          var email = (window.FMCD && FMCD.calendar && FMCD.calendar.email) || '';
          if (!ep){ slotsWrap.innerHTML = '<em>Availability not configured.</em>'; if (note) note.textContent=''; return; }
          var u = ep+'?action=availability&email='+encodeURIComponent(email)+'&date='+encodeURIComponent(date);
          fetch(u).then(function(r){ return r.json(); }).then(function(j){ if (j && j.ok && j.slots) render(j.slots); else { slotsWrap.innerHTML = '<em>Could not load times.</em>'; if (note) note.textContent=''; } }).catch(function(){ slotsWrap.innerHTML = '<em>Could not load times.</em>'; if (note) note.textContent=''; });
        }
        var rest = (window.FMCD && FMCD.ajax && FMCD.ajax.url) ? (FMCD.ajax.url + '/availability?date=' + encodeURIComponent(date)) : '';
        if (rest){
          fetch(rest).then(function(r){ return r.json(); }).then(function(res){ if (res && res.ok && res.slots) render(res.slots); else fallback(); }).catch(function(){ fallback(); });
        } else { fallback(); }
      }catch(e){}
    };
    
    // Global capture listener to trigger availability fetch when date changes
    document.addEventListener('change', function(e){
      if (!e || !e.target) return;
      var target = e.target;
      if (target.matches && target.matches('.fmcd-date')){
        var f = target.closest('.fmcd-form'); if (f) window.fmcdInlineFetchSlots(f);
      }
      var form = target.closest && target.closest('.fmdd-form');
      if (!form) return;
      if (target.matches('input[type="checkbox"]') && target.closest('[data-required-group]')){
        var group = target.closest('[data-required-group]');
        if (group){
          group.classList.remove('fmdd-group-error');
          var msg = group.querySelector('.fmdd-error-msg');
          if (msg) msg.classList.remove('visible');
          var inputs = group.querySelectorAll('input[type="checkbox"]');
          for (var i=0; i<inputs.length; i++){ if (inputs[i].setCustomValidity) inputs[i].setCustomValidity(''); }
        }
      } else if (target.matches('input[type="radio"]') && target.closest('[data-required-radio]')){
        var rgroup = target.closest('[data-required-radio]');
        if (rgroup){
          rgroup.classList.remove('fmdd-group-error');
          var rmsg = rgroup.querySelector('.fmdd-error-msg');
          if (rmsg) rmsg.classList.remove('visible');
          var radios = rgroup.querySelectorAll('input[type="radio"]');
          for (var j=0; j<radios.length; j++){ if (radios[j].setCustomValidity) radios[j].setCustomValidity(''); }
        }
      } else if (target.matches('input, select, textarea')){
        window.fmddClearFieldError(target);
      }
    }, true);

    document.addEventListener('input', function(e){
      if (!e || !e.target) return;
      var target = e.target;
      if (!target.closest || !target.closest('.fmdd-form')) return;
      window.fmddClearFieldError(target);
    }, true);
    
    JS;
      wp_add_inline_script('fmcd-form', $inline, 'after');
    
      // Localized config as you already had
      $opts = [
        'ajax' => [
          'url'   => rest_url('fmcd/v1'),
          'nonce' => wp_create_nonce('wp_rest')
        ],
        'calendar' => [
          'email'     => get_option('fmcd_calendar_email', 'info@foundationsmarketing.co.uk'),
          'endpoint'  => esc_url_raw(get_option('fmcd_calendar_endpoint', '')),
          'tz'        => 'Europe/London',
          'startHour' => 15,
          'endHour'   => 20
        ],
        'i18n' => [
          'sending'   => 'Sending...',
          'sent'      => 'Thank you! We’ll be in touch.',
          'booked'    => 'Booked. You’ll receive a confirmation email.',
          'required'  => 'This field is required',
          'tentative' => 'Tentative until calendar is connected.'
        ]
      ];
      wp_localize_script('fmcd-form', 'FMCD', $opts);
    }


  public function shortcode_contact_page() {
    $email = sanitize_email((string) get_option('fmcd_to_email', get_option('admin_email')));

    return '<section class="fmcd-page">'
      . '<header class="fmcd-page__intro">'
      . '<p class="fmcd-page__eyebrow">Start a conversation</p>'
      . '<h1 class="fmcd-page__title">Tell us what you are building.</h1>'
      . '<p class="fmcd-page__lede">Share where your business is now and what you need from the website. We will reply with the clearest next step.</p>'
      . ($email !== '' ? '<p class="fmcd-page__email"><span>Email</span><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>' : '')
      . '</header>'
      . $this->shortcode_contact()
      . '</section>';
  }

  public function shortcode_contact() {
    wp_enqueue_style('fmcd-form'); wp_enqueue_script('fmcd-form');
    ob_start(); ?>
    <form method="post" class="fmcd-form fmcd-contact" data-form="contact">
      <label>Name
        <input type="text" name="full_name" placeholder="Enter your name..." required>
      </label>
    
      <label>Email
        <input type="email" name="email" placeholder="Enter your email" required>
      </label>
    
      <label>Social Media
        <input type="text" name="social" placeholder="@yoursocialmedia, yourwebsite.com, etc">
      </label>
    
      <label>Website
        <input type="text" name="website" placeholder="your url or domain name (website.com)">
      </label>
    
      <label style="margin:0px;">What services are you interested in?<span class="fmcd-req">(required)</span></label>
      <div class="fmcd-pills" aria-required="true">
        <!-- single-select pills (radio) to mirror screenshot -->
        <input type="radio" id="plan-root"  name="services_plan" value="Root"  required hidden>
        <label for="plan-root"  class="fmcd-pill">Root</label>
    
        <input type="radio" id="plan-grow"  name="services_plan" value="Grow"  required hidden>
        <label for="plan-grow"  class="fmcd-pill">Grow</label>
    
        <input type="radio" id="plan-rise"  name="services_plan" value="Rise"  required hidden>
        <label for="plan-rise"  class="fmcd-pill">Rise</label>
      </div>
    
      <label>Tell us a little about your business. <span class="fmcd-req">(required)</span>
        <textarea name="about" rows="3" placeholder="Your industry, your preferred booking process, etc?" required></textarea>
      </label>
    
      <label>Can you give us more information regarding the stage your business is in at the moment?
        <textarea name="stage_details" rows="3" placeholder="Are you currently taking bookings, are you just starting out, etc"></textarea>
      </label>
    
      <label>What is your budget range?
        <input type="text" name="budget" placeholder="e.g., GBP 1–2k">
      </label>
    
      <label>How did you hear about us?
        <select name="ref_source">
          <option value="">Select one</option>
          <option>Instagram</option>
          <option>Google</option>
          <option>Referral</option>
          <option>Other</option>
        </select>
      </label>
    
      <label>When is your ideal project finish date? <span class="fmcd-req">(required)</span>
        <input type="date" name="deadline" placeholder="dd/mm/yyyy" required>
      </label>
    
      <label class="fmcd-meet">
        <input type="checkbox" name="want_meeting" value="yes" onchange="(function(c){var f=c.closest('.fmcd-form');var box=f&&f.querySelector('.fmcd-appointment');if(!box)return;if(c.checked){box.classList.remove('hidden');var date=box.querySelector('.fmcd-date');if(date&&!date.value){var t=new Date();var y=t.getFullYear(),m=('0'+(t.getMonth()+1)).slice(-2),d=('0'+t.getDate()).slice(-2);date.value=y+'-'+m+'-'+d;} if(date){date.dispatchEvent(new Event('change',{bubbles:true}));}} else {box.classList.add('hidden');}})(this)"> I’d like to book an appointment
      </label>
    
      <!-- Appointment picker (unchanged behavior; UX improved already) -->
      <div class="fmcd-appointment hidden" aria-live="polite">
        <h4>Choose a time (3–8 PM London)</h4>
        <div class="fmcd-date-time">
          <div class="fmcd-date-row">
            <span class="fmcd-date-icon" aria-hidden="true"></span>
            <input type="date" class="fmcd-date" name="appt_date" aria-label="Choose date">
          </div>
          <p class="fmcd-note"></p>
          <div class="fmcd-slots"></div>
          <input type="hidden" name="appt_iso">
        </div>
      </div>
    
      <button type="submit" class="fmcd-submit">Submit</button>
      <div class="fmcd-status" aria-live="polite"></div>
</form>

<div class="fmcd-modal" hidden>
  <div class="fmcd-modal__card">
    <h3>Thank you!</h3>
    <p>We received your message. We’ll get back to you shortly.</p>
    <button class="fmcd-modal__close" type="button">Close</button>
  </div>
</div>

    <?php
    return ob_get_clean();
  }

  public function shortcode_discovery_page() {
    return '<section class="fmdd-page">'
      . '<header class="fmdd-page__intro">'
      . '<p class="fmdd-page__eyebrow">Website discovery</p>'
      . '<h1 class="fmdd-page__title">Tell us about your business.</h1>'
      . '<p class="fmdd-page__lede">Share the content, style and setup details we need to turn your chosen template into your website. You can save complex answers elsewhere and paste them in when you are ready.</p>'
      . '</header>'
      . $this->shortcode_discovery()
      . '</section>';
  }

  public function shortcode_discovery() {
    wp_enqueue_style('fmcd-form'); wp_enqueue_script('fmcd-form');

    $templates = apply_filters('fmcd/discovery_templates', [
      ['value' => 'Aether',      'label' => 'Aether',      'colors' => 4, 'url' => 'https://foundationsmarketing.co.uk/template/aether/',      'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Aether_Mark-scaled.webp',      'image_count' => 7],
      ['value' => 'Birth Space', 'label' => 'Birth Space', 'colors' => 4, 'url' => 'https://foundationsmarketing.co.uk/template/birth-space/', 'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/BirthSpace_Mark-scaled.webp', 'image_count' => 8],
      ['value' => 'Bloom',       'label' => 'Bloom',       'colors' => 2, 'url' => 'https://foundationsmarketing.co.uk/template/bloom/',       'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Bloom_Mark-scaled.webp',       'image_count' => 7],
      ['value' => 'Lumen',       'label' => 'Lumen',       'colors' => 4, 'url' => 'https://foundationsmarketing.co.uk/template/lumen/',       'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Lumen_Mark.webp',           'image_count' => 7],
      ['value' => 'Nova',        'label' => 'Nova',        'colors' => 3, 'url' => 'https://foundationsmarketing.co.uk/template/nova/',        'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Nova_Mark-scaled.webp',        'image_count' => 14],
      ['value' => 'Sequoia',     'label' => 'Sequoia',     'colors' => 4, 'url' => 'https://foundationsmarketing.co.uk/template/sequoia/',     'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Sequoia_Mark-1-scaled.webp',    'image_count' => 7],
      ['value' => 'Solis',       'label' => 'Solis',       'colors' => 4, 'url' => 'https://foundationsmarketing.co.uk/template/solis/',       'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Solis_Mark-image-scaled.webp',  'image_count' => 7],
      ['value' => 'Solstice',    'label' => 'Solstice',    'colors' => 5, 'url' => 'https://foundationsmarketing.co.uk/template/solstice/',    'image_guide' => 'https://foundationsmarketing.co.uk/wp-content/uploads/2025/11/Solstice_Mark-scaled.webp',     'image_count' => 11],
    ]);

    $asset_items = apply_filters('fmcd/discovery_assets', [
      'logo'          => ['label' => 'Logo',                    'upload_label' => 'Upload logo file'],
      'hosting'       => ['label' => 'Hosting access',          'upload_label' => 'Upload hosting access (PDF/Doc)'],
      'domain'        => ['label' => 'Domain access',           'upload_label' => 'Upload domain access (PDF/Doc)'],
      'brand'         => ['label' => 'Brand colours / palette', 'upload_label' => 'Upload brand colours or guidelines'],
      'photo_self'    => ['label' => 'Photos of yourself',      'upload_label' => 'Upload photos of yourself'],
      'photo_space'   => ['label' => 'Photos of your space',    'upload_label' => 'Upload workspace photos'],
      'business_email'=> ['label' => 'Business email details',  'upload_label' => 'Upload business email info'],
      'social'        => ['label' => 'Social media links',      'upload_label' => 'Upload social media info'],
      'testimonials'  => ['label' => 'Testimonials',            'upload_label' => 'Upload testimonials'],
      'booking'       => ['label' => 'Booking system setup',    'upload_label' => 'Upload booking system info'],
    ]);

    ob_start(); ?>
  <form method="post" class="fmdd-form" data-form="discovery" enctype="multipart/form-data">
    <div class="fmdd-steps">
      <button type="button" class="active" data-step="1">1. You & Business</button>
      <button type="button" data-step="2">2. Services & Audience</button>
      <button type="button" data-step="3">3. Style Direction</button>
      <button type="button" data-step="4">4. Website Setup</button>
      <button type="button" data-step="5">5. Content</button>
    </div>

    <!-- STEP 1 -->
    <section class="fmdd-step" data-step="1">
      <label>1. Full name<span class="fmdd-req">*</span>
        <input type="text" name="full_name" required>
      </label>

      <label>2. Business email address<span class="fmdd-req">*</span>
        <input type="email" name="business_email" required>
      </label>

      <label>3. Business name
        <input type="text" name="business_name" placeholder="Put N/A if you haven't decided yet">
      </label>

      <label>4. What is your profession?<span class="fmdd-req">*</span>
        <textarea name="profession_services" rows="3" placeholder="e.g., Doula, Sleep Coach, Hypnobirthing instructor" required></textarea>
      </label>

      <label class="qualified-label">5. How long have you been working?<span class="fmdd-req">*</span></label>
      <div class="fmdd-group fmdd-group-inline fmdd-experience">
        <label class="fmdd-radio qualified-radio"><input type="radio" name="experience_length" value="Recently qualified" required> <span>Recently qualified</span></label>
        <label class="fmdd-radio qualified-radio"><input type="radio" name="experience_length" value="6-12 months"> <span>6–12 months</span></label>
        <label class="fmdd-radio qualified-radio"><input type="radio" name="experience_length" value="2 years or more"> <span>2 years</span></label>
      </div>

      <div class="fmdd-nav">
        <button type="button" class="next" data-fmdd="next" onclick="fmddInlineNext(this)">Next</button>
      </div>
    </section>

    <!-- STEP 2 -->
    <section class="fmdd-step hidden" data-step="2">
      <label class="fmdd-section-title">6. What services/treatments/products do you want to be listed on the website?<span class="fmdd-req">*</span></label>
      <div class="fmdd-services" data-max="5">
        <div class="fmdd-service-row" data-index="1">
          <p class="fmdd-service-heading">Item 1</p>
          <div class="fmdd-service-fields">
            <label class="service-title-label">Item title<span class="fmdd-req">*</span>
              <input type="text" name="service_title[]" required>
            </label>
            <label class="service-title-label">Description<span class="fmdd-req">*</span>
              <textarea name="service_description[]" rows="2" maxlength="200" placeholder="Max 200 characters" required></textarea>
            </label>
            <label class="service-title-label">Price
              <input type="text" name="service_price[]" placeholder="e.g., £120 / session">
            </label>
          </div>
        </div>
      </div>
      <button type="button" class="fmdd-add-service">+ Add another item</button>

      <label>7. Describe your ideal client<span class="fmdd-req">*</span>
        <textarea name="ideal_client" rows="3" required></textarea>
      </label>

      <label>8. How do clients work with you?<span class="fmdd-req">*</span>
        <textarea name="client_process" rows="3" placeholder="Share the main flows" required></textarea>
      </label>

      <label>9. Where are you based?<span class="fmdd-req">*</span>
        <br/><small class="fmdd-note">If you want your physical address listed on the website please input below, alternatively just put your postcode for SEO purposes. If you only offer online services, choose “Online only”.</small>
      </label>
      <div class="fmdd-group fmdd-group-inline fmdd-location-choice" data-required-radio data-required-message="Please choose physical address or online services.">
        <label class="fmdd-radio location-radio"><input type="radio" name="location_mode" value="physical" required> <span>Physical Address</span></label>
        <label class="fmdd-radio location-radio"><input type="radio" name="location_mode" value="online"> <span>Online Services Only</span></label>
      </div>
      <div class="fmdd-location-grid">
        <label>Street Address
          <input type="text" name="location_address" placeholder="123 Main Street">
        </label>
        <label>State / Region
          <input type="text" name="location_state" placeholder="State / Region">
        </label>
        <label>Postal / Zip Code<span class="fmdd-req">*</span>
          <input type="text" name="location_zip" placeholder="Postal / Zip Code" required data-error="Please add your postcode.">
        </label>
      </div>

      <div class="fmdd-nav">
        <button type="button" class="back" data-fmdd="back" onclick="fmddInlineBack(this)">Back</button>
        <button type="button" class="next" data-fmdd="next" onclick="fmddInlineNext(this)">Next</button>
      </div>
    </section>

    <!-- STEP 3 -->
    <section class="fmdd-step hidden" data-step="3">
      <label>10. Choose your template<span class="fmdd-req">*</span>
        <select name="template_choice" class="fmdd-template" required>
          <option value="">Select one</option>
          <?php foreach ($templates as $tpl): ?>
            <option value="<?php echo esc_attr($tpl['value']); ?>"
              data-colors="<?php echo esc_attr($tpl['colors']); ?>"
              data-url="<?php echo esc_url($tpl['url']); ?>"
              data-image-guide="<?php echo esc_url($tpl['image_guide']); ?>"
              data-image-count="<?php echo esc_attr($tpl['image_count']); ?>">
              <?php echo esc_html($tpl['label']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="fmdd-template-link" aria-live="polite"></div>

      <label>11. Do you have your own colours for the theme?<span class="fmdd-req">*</span></label>
      <div class="fmdd-group fmdd-group-inline fmdd-color-choice" data-required-radio data-required-message="Please choose an option for colours.">
        <label class="fmdd-radio"><input type="radio" name="own_colors" value="yes" required> <span>Yes</span></label>
        <label class="fmdd-radio"><input type="radio" name="own_colors" value="no"> <span>No — follow the template colours</span></label>
      </div>
      <div class="fmdd-sub fmdd-color-wrap hidden">
        <p class="fmdd-note">Enter your colours:</p>
        <p class="fmdd-note fmdd-color-note" aria-live="polite" hidden>This template has <strong><span class="fmdd-color-required">0</span></strong> colours please fill them all.</p>
        <div class="fmdd-color-fields"></div>
      </div>

      <label>12. Do you have your own fonts for the theme?<span class="fmdd-req">*</span></label>
      <div class="fmdd-group fmdd-group-inline fmdd-font-choice" data-required-radio data-required-message="Please choose an option for fonts.">
        <label class="fmdd-radio"><input type="radio" name="own_fonts" value="yes" required> <span>Yes — I have my own fonts</span></label>
        <label class="fmdd-radio"><input type="radio" name="own_fonts" value="no"> <span>No — follow the template font</span></label>
      </div>
      <div class="fmdd-sub fmdd-font-wrap hidden">
        <p class="fmdd-note">Recommended: 2 main fonts (heading + body). You can add up to 4 including accent.</p>
        <div class="fmdd-fonts">
          <div class="fmdd-font-row"><input type="text" name="fonts[]" placeholder="e.g., Playfair Display"></div>
          <div class="fmdd-font-row"><input type="text" name="fonts[]" placeholder="e.g., Inter"></div>
        </div>
        <div class="fmdd-font-actions">
          <button type="button" class="fmdd-add-font">+ Add another font</button>
        </div>
      </div>

      <div class="fmdd-nav">
        <button type="button" class="back" data-fmdd="back" onclick="fmddInlineBack(this)">Back</button>
        <button type="button" class="next" data-fmdd="next" onclick="fmddInlineNext(this)">Next</button>
      </div>
    </section>

    <!-- STEP 4 -->
    <section class="fmdd-step hidden" data-step="4">
      <fieldset class="fmdd-credential" data-credential="domain">
        <legend>13. Domain registrar access<span class="fmdd-req">*</span></legend>
        <div class="fmdd-group fmdd-group-inline" data-required-radio data-required-message="Please let us know if you can share domain access.">
          <label class="fmdd-radio"><input type="radio" name="domain_status" value="have" required data-credential-status="domain"> <span>I can share login details</span></label>
          <label class="fmdd-radio"><input type="radio" name="domain_status" value="need" data-credential-status="domain"> <span>I don't have this yet</span></label>
        </div>
        <div class="fmdd-credential-fields hidden" data-credential-fields="domain">
          <label>Domain provider<span class="fmdd-req">*</span>
            <input type="text" name="domain_provider" placeholder="Registrar name (e.g., GoDaddy)" data-error="Please add your domain provider.">
          </label>
          <label>Domain username<span class="fmdd-req">*</span>
            <input type="text" name="domain_username" placeholder="Registrar username" data-error="Please add your domain username.">
          </label>
          <p class="fmcd-note">We will arrange delegated access or a secure handover after reviewing your request. Please do not send passwords in this form.</p>
        </div>
        <p class="fmdd-note fmdd-credential-note hidden" data-credential-recommendation="domain">Need a domain? We recommend getting started with Bluehost or Hostinger.</p>
      </fieldset>

      <fieldset class="fmdd-credential" data-credential="hosting">
        <legend>14. Hosting account access<span class="fmdd-req">*</span></legend>
        <div class="fmdd-group fmdd-group-inline" data-required-radio data-required-message="Please let us know if you can share hosting access.">
          <label class="fmdd-radio"><input type="radio" name="hosting_status" value="have" required data-credential-status="hosting"> <span>I can share login details</span></label>
          <label class="fmdd-radio"><input type="radio" name="hosting_status" value="need" data-credential-status="hosting"> <span>I don't have this yet</span></label>
        </div>
        <div class="fmdd-credential-fields hidden" data-credential-fields="hosting">
          <label>Hosting provider<span class="fmdd-req">*</span>
            <input type="text" name="hosting_provider" placeholder="Hosting provider (e.g., Hostinger)" data-error="Please add your hosting provider.">
          </label>
          <label>Hosting username<span class="fmdd-req">*</span>
            <input type="text" name="hosting_username" placeholder="Hosting username" data-error="Please add your hosting username.">
          </label>
          <p class="fmcd-note">We will arrange delegated access or a secure handover after reviewing your request. Please do not send passwords in this form.</p>
        </div>
        <p class="fmdd-note fmdd-credential-note hidden" data-credential-recommendation="hosting">Need hosting? We recommend getting started with Hostinger or Bluehost.</p>
      </fieldset>

      <fieldset class="fmdd-credential" data-credential="email">
        <legend>15. Business email access<span class="fmdd-req">*</span></legend>
        <div class="fmdd-group fmdd-group-inline" data-required-radio data-required-message="Please let us know if you can share business email access.">
          <label class="fmdd-radio"><input type="radio" name="email_status" value="have" required data-credential-status="email"> <span>I can share login details</span></label>
          <label class="fmdd-radio"><input type="radio" name="email_status" value="need" data-credential-status="email"> <span>I don't have this yet</span></label>
        </div>
        <div class="fmdd-credential-fields hidden" data-credential-fields="email">
          <label>Email platform<span class="fmdd-req">*</span>
            <input type="text" name="email_platform" placeholder="Platform name" data-error="Please add your email platform.">
          </label>
          <label>Email username<span class="fmdd-req">*</span>
            <input type="text" name="email_username" placeholder="Login username" data-error="Please add your email username.">
          </label>
          <p class="fmcd-note">We will arrange delegated access or a secure handover after reviewing your request. Please do not send passwords in this form.</p>
        </div>
        <p class="fmdd-note fmdd-credential-note hidden" data-credential-recommendation="email">Need a business email? We recommend Google.</p>
      </fieldset>

      <fieldset class="fmdd-credential" data-credential="booking">
        <legend>16. Booking system access<span class="fmdd-req">*</span></legend>
        <div class="fmdd-group fmdd-group-inline" data-required-radio data-required-message="Please let us know if you can share booking system access.">
          <label class="fmdd-radio"><input type="radio" name="booking_status" value="have" required data-credential-status="booking"> <span>I can share login details</span></label>
          <label class="fmdd-radio"><input type="radio" name="booking_status" value="need" data-credential-status="booking"> <span>I don't have this yet</span></label>
        </div>
        <div class="fmdd-credential-fields hidden" data-credential-fields="booking">
          <label>Booking platform<span class="fmdd-req">*</span>
            <input type="text" name="booking_platform" placeholder="Platform name" data-error="Please add your booking platform.">
          </label>
          <label>Booking username<span class="fmdd-req">*</span>
            <input type="text" name="booking_username" placeholder="Login username" data-error="Please add your booking username.">
          </label>
          <p class="fmcd-note">We will arrange delegated access or a secure handover after reviewing your request. Please do not send passwords in this form.</p>
        </div>
        <p class="fmdd-note fmdd-credential-note hidden" data-credential-recommendation="booking">Need a booking system? We recommend Calendly or similar easy-to-use services.</p>
      </fieldset>

      <fieldset class="fmdd-assets" data-required-group data-required-message="Select at least one item so we know what you already have ready.">
        <legend>17. Please click the items you already have on hand.</legend>
        <small class="fmdd-note">When selected, an upload field will appear for that item (you can upload multiple files, each max 20MB).</small>
        <?php foreach ($asset_items as $slug => $item): ?>
          <div class="fmdd-asset" data-asset="<?php echo esc_attr($slug); ?>">
            <label class="fmdd-check">
              <input type="checkbox" name="assets_have[]" value="<?php echo esc_attr($item['label']); ?>" data-asset-toggle="<?php echo esc_attr($slug); ?>">
              <span><?php echo esc_html($item['label']); ?></span>
            </label>
            <?php if (!empty($item['upload_label'])): ?>
              <div class="fmdd-sub fmdd-asset-upload hidden" data-asset-upload="<?php echo esc_attr($slug); ?>">
                <div class="fmdd-file">
                  <span class="fmdd-file-copy"><em><?php echo esc_html($item['upload_label']); ?> (you can upload multiple files, each max 20MB)</em></span>
                  <label class="fmdd-file-trigger">
                    <span class="fmdd-file-button">upload file</span>
                    <input type="file" name="asset_uploads[<?php echo esc_attr($slug); ?>][]" data-max-bytes="20971520" data-error="Please upload this file." multiple>
                  </label>
                  <span class="fmdd-file-name" data-placeholder="No files chosen">No files chosen</span>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </fieldset>

      <div class="fmdd-nav">
        <button type="button" class="back" data-fmdd="back" onclick="fmddInlineBack(this)">Back</button>
        <button type="button" class="next" data-fmdd="next" onclick="fmddInlineNext(this)">Next</button>
      </div>
    </section>

    <!-- STEP 5 -->
    <section class="fmdd-step hidden" data-step="5">
      <label>18. Do you have images for the template?<span class="fmdd-req">*</span></label>
      <div class="fmdd-group fmdd-group-inline fmdd-images-toggle" data-required-radio data-required-message="Please let us know if you already have images.">
        <label class="fmdd-radio"><input type="radio" name="have_images" value="yes" required> <span>Yes</span></label>
        <label class="fmdd-radio"><input type="radio" name="have_images" value="freepik"> <span>No – I want to choose from Freepik stock images</span></label>
      </div>

      <div class="fmdd-template-summary" aria-live="polite">
        <p>You have chosen <strong><span class="fmdd-template-chosen">No template selected yet</span></strong>
          <a href="#" class="fmdd-template-view" target="_blank" rel="noopener" hidden>View template</a>
        </p>
        <p class="fmdd-note">Once you pick a template we’ll recap the highlights here.</p>
      </div>

      <div class="fmdd-template-images" aria-live="polite">
        <p>
          <span class="fmdd-template-image-text">Select a template to see the image requirements.</span>
          <a href="#" class="fmdd-image-guide" target="_blank" rel="noopener" hidden>Click this guide to see the image and text map</a>
        </p>
      </div>
 
      <div class="fmdd-images-have hidden">
        <p class="fmdd-note">Add a public link to your image folder so we can download everything in one go.</p>

        <label>Link to your image folder<span class="fmdd-req">*</span>
          <input type="url" name="image_drive_link" placeholder="Link to your drive/dropbox URL" data-error="Please add your Drive/Dropbox link.">
        </label>
      </div>
 
      <div class="fmdd-images-freepik hidden">
        <p class="fmdd-note">We can source photos for you with our <a href="https://www.freepik.com" target="_blank" rel="noopener">Freepik account</a>. Search for images you like, copy the Freepik links, and paste them below.</p>
        <div class="fmdd-image-links"></div>
      </div>

      <label>19. Upload website copy – following the guide above<span class="fmdd-req">*</span>
        <input type="url" name="content_link" placeholder="Link to your drive/dropbox URL" required data-error="Please add your content link.">
      </label>
      <div class="fmdd-file">
        <span class="fmdd-file-copy"><em>Or upload website content documents (each max 20MB)</em></span>
        <label class="fmdd-file-trigger">
          <span class="fmdd-file-button">upload file</span>
          <input type="file" name="content_file[]" data-max-bytes="20971520" multiple>
        </label>
        <span class="fmdd-file-name" data-placeholder="No files chosen">No files chosen</span>
      </div>

      <label>20. Upload your client reviews or testimonials in text or screenshot form
        <input type="url" name="review_link" placeholder="Link to your drive/dropbox URL">
      </label>
      <div class="fmdd-file">
        <span class="fmdd-file-copy"><em>Or upload review files (each max 20MB)</em></span>
        <label class="fmdd-file-trigger">
          <span class="fmdd-file-button">upload file</span>
          <input type="file" name="review_file[]" data-max-bytes="20971520" multiple>
        </label>
        <span class="fmdd-file-name" data-placeholder="No files chosen">No files chosen</span>
      </div>

      <div class="fmdd-nav">
        <button type="button" class="back" data-fmdd="back" onclick="fmddInlineBack(this)">Back</button>
        <button type="submit" class="fmdd-submit">Submit</button>
      </div>
      <div class="fmdd-status" aria-live="polite"></div>
    </section>
  </form>

  <div class="fmcd-modal" hidden>
    <div class="fmcd-modal__card">
      <h3>Thank you!</h3>
      <p>Your discovery form has been submitted successfully.</p>
      <button class="fmcd-modal__close" type="button">Close</button>
    </div>
  </div>
    <?php
    return ob_get_clean();
  }

  public function handle_mailchimp_sync($type, $data){
    if (!class_exists('FMCD_Integrations')) return;
    try {
      FMCD_Integrations::mailchimp_sync($type, is_array($data) ? $data : []);
    } catch (\Throwable $e) {
      error_log('[FMCD] Async Mailchimp exception: '.$e->getMessage());
    }
  }
}
new FMCD_Plugin;
