// A stable request identity lasts until success, so retries do not duplicate leads.
window.fmcdRequestId = function(form) {
  if (!form.dataset.requestId) form.dataset.requestId = window.crypto?.randomUUID?.() || (Date.now().toString(36)+'-'+Math.random().toString(36).slice(2)+'-'+Math.random().toString(36).slice(2));
  return form.dataset.requestId;
};
(function($){
  'use strict';

  // --- Helpers ---
  function showModal($form){
    // Find a nearby modal robustly (builders may wrap/move nodes)
    let $m = $form.nextAll('.fmcd-modal').first();
    if (!$m.length) $m = $form.parent().children('.fmcd-modal').first();
    if (!$m.length) $m = $form.closest('.fmdd-form, .fmcd-form').nextAll('.fmcd-modal').first();
    if (!$m.length) $m = jQuery('.fmcd-modal').first();
    if ($m && $m.length) {
      $m.removeAttr('hidden');
    $m.find('.fmcd-modal__close').off('click').on('click', ()=> $m.attr('hidden', true));
    }
  }
  window.showModal = showModal;
  function gather($form){
    const fd = new FormData($form[0]); const o = {};
    for (let [k,v] of fd.entries()){ if (o[k] !== undefined){ if(!Array.isArray(o[k])) o[k]=[o[k]]; o[k].push(v); } else o[k]=v; }
    return o;
  }

  // --- Availability fetching with loading states ---
  function fetchSlots(date, cb){
    return $.ajax({url:FMCD.ajax.url+'/availability',data:{date},dataType:'json',timeout:15000})
      .done(res => res && res.ok && res.slots ? cb(null,res.slots) : cb(new Error(res?.error || 'Availability unavailable')))
      .fail(() => cb(new Error('Could not load times. Please try again.')));
  }

  function renderSlotsState($wrap, state, slots){
    const $c = $wrap.find('.fmcd-slots').empty();
    const $note = $wrap.find('.fmcd-note');

    if (state === 'idle'){
      $note.text('Choose a date to see available times.');
      $c.html('<div class="fmcd-slots-placeholder"></div>');
      return;
    }
    if (state === 'loading'){
      $note.text('Loading times…');
      // skeleton shimmer
      let skeleton = '';
      for (let i=0;i<6;i++){ skeleton += '<span class="fmcd-skel"></span>'; }
      $c.html('<div class="fmcd-skeleton">'+skeleton+'</div>');
      return;
    }
    if (state === 'error'){
      $note.text('Couldn’t load times. Please try another date.');
      $c.html('<div class="fmcd-slots-placeholder"></div>');
      return;
    }
    if (state === 'ready'){
      if (!slots || !slots.length){
        $note.text('No times available for this date.');
        $c.html('<div class="fmcd-slots-placeholder"></div>');
        return;
      }
      $note.text('Select a time:');
      slots.forEach(s=>{
        const st = new Date(s.start);
        const label = st.toLocaleTimeString('en-GB', {hour:'2-digit', minute:'2-digit', timeZone:'Europe/London'});
        const $btn = $('<button type="button" class="fmcd-slot">').text(label).data('slot', s);
        if(!s.available) $btn.prop('disabled', true).addClass('is-disabled').attr('title','Unavailable');
        $c.append($btn);
      });
    }
  }

  // --- Appointment UI wiring (contact form) ---
  function attachAppointment($form){
    const $toggle = $form.find('[name=want_meeting]');
    const $box    = $form.find('.fmcd-appointment');
    const $date   = $form.find('.fmcd-date');
    const $slotsC = $form.find('.fmcd-slots');
    const $note   = $form.find('.fmcd-note');
    const $iso    = $form.find('[name=appt_iso]');

    // Large click target to open datepicker
    $box.find('.fmcd-date-row').off('click').on('click', function(e){
      if (e.target.tagName.toLowerCase() !== 'input') $date.trigger('focus').trigger('click');
    });

    function ensureTodayPrefill(){
      if (!$date.val()){
        const t = new Date();
        const y=t.getFullYear(), m=String(t.getMonth()+1).padStart(2,'0'), d=String(t.getDate()).padStart(2,'0');
        $date.val(`${y}-${m}-${d}`);
      }
    }

    function clearSelection(){
      $iso.val('');
      $form.removeData('slotEnd');
      $slotsC.find('.fmcd-slot').removeClass('selected');
    }

    // Initial idle state
    renderSlotsState($box, 'idle');

    $toggle.on('change', function(){
      if (this.checked) {
        $box.removeClass('hidden');
        // Prefill today and immediately fetch
        ensureTodayPrefill();
        clearSelection();
        renderSlotsState($box, 'loading');
        const requestedDate = $date.val();
        fetchSlots(requestedDate, function(err, slots){
          if (!$toggle.prop('checked') || $date.val() !== requestedDate) return;
          if (err) { renderSlotsState($box, 'error'); return; }
          renderSlotsState($box, 'ready', slots);
        });
      } else {
        $box.addClass('hidden');
        clearSelection();
        renderSlotsState($box, 'idle');
      }
    });

    // Fetch when date changes
    let fetchTimer = null;
    $date.on('change input', function(){
      clearSelection();
      renderSlotsState($box, 'loading');
      const d = this.value;
      window.clearTimeout(fetchTimer);
      fetchTimer = window.setTimeout(function(){
        fetchSlots(d, function(err, slots){
          if (!$toggle.prop('checked') || $date.val() !== d) return;
          if (err) { renderSlotsState($box, 'error'); return; }
          renderSlotsState($box, 'ready', slots);
        });
      }, 150); // tiny debounce for UX
    });

    // Select slot
    $form.on('click', '.fmcd-slot', function(){
      $form.find('.fmcd-slot').removeClass('selected');
      $(this).addClass('selected');
      const slot = $(this).data('slot');
      $iso.val(slot.start);
      $form.data('slotEnd', slot.end);
    });
  }

  // --- Submit Contact ---
  function sendForm($form, payload, done){
    payload.request_id = window.fmcdRequestId($form[0]);
    $.ajax({
      method: 'POST',
      url: FMCD.ajax.url + '/submit',
      headers: {'X-WP-Nonce': FMCD.ajax.nonce},
      data: payload
    }).done(res=>done(null,res))
      .fail(res=>done(new Error(res.responseJSON?.error || 'Submit failed. Your answers have been kept; please retry.')));
  }

  function bookSlot($form, basePayload, cb){
      const iso = $form.find('[name=appt_iso]').val();
      // carry contact fields so server can email a single full report
      const extras = {
        social:        basePayload.social || '',
        website:       basePayload.website || '',
        services_plan: basePayload.services_plan || '',
        about:         basePayload.about || '',
        stage_details: basePayload.stage_details || '',
        budget:        basePayload.budget || '',
        ref_source:    basePayload.ref_source || '',
        deadline:      basePayload.deadline || ''
      };
    
      if (!iso) { cb(null, {ok:true,booked:false}); return; }
      var dataAppt = {
          request_id: window.fmcdRequestId($form[0]),
          full_name: basePayload.full_name || basePayload['full_name'],
          email:     basePayload.email || basePayload['email'],
          phone:     basePayload.phone || basePayload['phone'] || '',
          startIso:  iso,
          endIso: (function(){
            const fromHidden = $form.data('slotEnd') || '';
            if (fromHidden) return fromHidden;
            try { const s=new Date(iso), e=new Date(s.getTime()+60*60000); return e.toISOString(); }
            catch(e){ return ''; }
        })()
      };
      for (var k2 in extras){ if (Object.prototype.hasOwnProperty.call(extras, k2)) dataAppt[k2] = extras[k2]; }
      $.ajax({
        method:'POST', url: FMCD.ajax.url + '/book',
        headers:{'X-WP-Nonce':FMCD.ajax.nonce},
        data: dataAppt
      }).done(res=>cb(null,res)).fail(()=>cb(new Error('Booking failed')));
    }


  function requireMeetingIfChecked($form){
    const wants = $form.find('[name=want_meeting]').prop('checked');
    if (!wants) return null; // ok
    const hasDate = !!$form.find('.fmcd-date').val();
    const hasIso  = !!$form.find('[name=appt_iso]').val();
    if (!hasDate){
      $form.find('.fmcd-note').text('Please pick a date first (loading times…)');
      $form.find('.fmcd-date').focus();
      return 'Please pick a date';
    }
    if (!hasIso){
      $form.find('.fmcd-note').text('Please choose a time slot.');
      return 'Please choose a time';
    }
    return null;
  }

  // --- Progressive steps (discovery) ---
  function progressiveSteps($form){
    const $steps = $form.find('.fmcd-steps button');
    function go(n){
      $form.find('.fmcd-step').addClass('hidden');
      $form.find('.fmcd-step[data-step="'+n+'"]').removeClass('hidden');
      $steps.removeClass('active').filter('[data-step="'+n+'"]').addClass('active');
    }
    $steps.on('click', function(){ go($(this).data('step')); });
    $form.on('click', '.fmcd-nav .next', function(){
      const cur = parseInt($form.find('.fmcd-step:not(.hidden)').data('step'),10); go(cur+1);
    });
    $form.on('click', '.fmcd-nav .back', function(){
      const cur = parseInt($form.find('.fmcd-step:not(.hidden)').data('step'),10); go(cur-1);
    });
  }

  function toggleSubs($form){
    function vis(radioName, value, targetSel){
      const v = $form.find('input[name="'+radioName+'"]:checked').val();
      $form.find(targetSel).toggleClass('hidden', v !== value);
    }
    $form.on('change', 'input[name=has_domain]', ()=> vis('has_domain','yes','.fmcd-domain'));
    $form.on('change', 'input[name=has_hosting]', ()=> vis('has_hosting','yes','.fmcd-hosting'));
  }

  // --- Boot ---
  $(function(){
    $('.fmcd-contact').each(function(){
      const $f = $(this);
      const inline = this.getAttribute && this.getAttribute('onsubmit');
      if (inline) $f.data('fmcd-inline', inline);
      $f.removeAttr('onsubmit');
      attachAppointment($f);

      // If user checks “book”, pre-open date + show loading immediately
      $f.on('change','[name=want_meeting]', function(){
        if (this.checked) {
          const $date = $f.find('.fmcd-date');
          if (!$date.val()) $date.trigger('change'); // will prefill & fetch via attachAppointment
        }
      });

      $f.on('submit', function(e){
        e.preventDefault();
        const $btn = $f.find('.fmcd-submit'); const $st = $f.find('.fmcd-status');
        if ($btn.prop('disabled')) return;
        // Required: if meeting checked, must choose date + time
        const meetErr = requireMeetingIfChecked($f);
        if (meetErr){ $st.text(meetErr); return; }

        $btn.prop('disabled', true).text(FMCD.i18n.sending);
        const payload = gather($f); payload.type = 'contact';

        sendForm($f, payload, function(err){
          if (err){ $st.text(err.message); $btn.prop('disabled', false).text('Submit'); return; }
          bookSlot($f, payload, function(be, br){
            $btn.prop('disabled', false).text('Submit');
            if (be || (br && br.ok===false)) {
              $st.text('Submitted, but booking failed: ' + (be? be.message : (br.error||'')));
              showModal($f);
            } else {
              $st.text(FMCD.i18n.sent);
              showModal($f);
              $f[0].reset();
              delete $f[0].dataset.requestId;
              $f.find('.fmcd-slots').empty();
              $f.find('.fmcd-appointment .fmcd-note').text('');
            }
          });
        });
      });
    });

  });

})(jQuery);


// ====== DISCOVERY (fmdd) — robust delegated bindings ======
(function($){
  'use strict';

  function fmddGo($form, step){
    const total = $form.find('.fmdd-step').length;
    const target = Math.max(1, Math.min(step, total));
    $form.find('.fmdd-step').addClass('hidden')
      .filter('[data-step="'+target+'"]').removeClass('hidden');
    $form.find('.fmdd-steps button').removeClass('active')
      .filter('[data-step="'+target+'"]').addClass('active');
    $form.attr('data-fmdd-step', target);
  }

  function fmddToggle($form, sel, show){
    const $el = $form.find(sel);
    if (!$el.length) return;
    $el.toggleClass('hidden', !show);
    $el.toggleClass('shows', !!show);
  }

  function fmddGetForm(el){
    return $(el).closest('.fmdd-form');
  }

  function fmddCountFonts($form){
    return $form.find('.fmdd-fonts .fmdd-font-row').length;
  }

  function fmddUpdateFontButtons($form){
    const $add = $form.find('.fmdd-add-font');
    if ($add.length) $add.prop('disabled', fmddCountFonts($form) >= 4);
    $form.find('.fmdd-fonts .fmdd-font-row').each(function(idx){
      const $row = $(this);
      $row.find('.fmdd-remove-font').remove();
      if (idx >= 2){
        $('<button type="button" class="fmdd-remove-font" aria-label="Remove font">Remove</button>')
          .appendTo($row);
      }
    });
  }

  function renderColorFields($form, count){
    const $wrap = $form.find('.fmdd-color-fields');
    if (!$wrap.length) return;
    const previous = [];
    $wrap.find('input[name="colors[]"]').each(function(){ previous.push($(this).val()); });
    const total = Math.max(1, Math.min(8, parseInt(count || 0, 10) || 3));
    $wrap.empty();
    for (let i=1; i<=total; i++){
      const $row = $('<div class="fmdd-color-row"><input type="text" name="colors[]" placeholder="Color '+i+' (e.g., #D6421E)"></div>');
      if (previous[i-1]) $row.find('input').val(previous[i-1]);
      $wrap.append($row);
    }
  }

  function getTemplateData($form){
    const $sel = $form.find('.fmdd-template');
    if (!$sel.length) return {value:'', label:'', colors:0, url:'', imageGuide:'', imageCount:0};
    const $opt = $sel.find('option:selected');
    return {
      value: String($sel.val() || ''),
      label: $.trim($opt.text() || ''),
      colors: parseInt($opt.data('colors'), 10) || 0,
      url: $opt.data('url') || '',
      imageGuide: $opt.data('image-guide') || '',
      imageCount: parseInt($opt.data('image-count'), 10) || 0
    };
  }

  function updateTemplateDetails($form){
    const data = getTemplateData($form);
    const lastVal   = $form.data('tplValue');
    const lastCount = $form.data('tplImageCount');
    const lastUrl   = $form.data('tplUrl');
    const lastGuide = $form.data('tplGuide');

    $form.data('tplColorCount', data.colors || 0);

    if (data.value !== lastVal || data.url !== lastUrl){
      const $linkWrap = $form.find('.fmdd-template-link');
      if ($linkWrap.length){
        if (data.value){
          const link = data.url ? '<a href="'+data.url+'" target="_blank" rel="noopener">View template</a>' : '';
          $linkWrap.html(link);
        } else {
          $linkWrap.empty();
        }
      }

      const $summary = $form.find('.fmdd-template-summary');
      if ($summary.length){
        const $name = $summary.find('.fmdd-template-chosen');
        const $view = $summary.find('.fmdd-template-view');
        if (data.value){
          $name.text(data.label || data.value);
          if (data.url){
            $view.attr('href', data.url).prop('hidden', false);
          } else {
            $view.prop('hidden', true);
          }
        } else {
          $name.text('No template selected yet');
          $view.prop('hidden', true);
        }
      }
    }

    if (data.imageGuide !== lastGuide){
      const $guide = $form.find('.fmdd-image-guide');
      if ($guide.length){
        if (data.imageGuide){
          $guide.attr('href', data.imageGuide).prop('hidden', false);
        } else {
          $guide.prop('hidden', true);
        }
      }
    }

    const $imageText = $form.find('.fmdd-template-image-text');
    if ($imageText.length){
      if (data.value){
        if (data.imageCount > 0){
          const plural = data.imageCount === 1 ? 'image' : 'images';
          $imageText.html('This template needs <strong>'+data.imageCount+' '+plural+'</strong>.');
        } else {
          $imageText.text('Follow the photo guidance in the guide sent via email.');
        }
      } else {
        $imageText.text('Select a template to see the image requirements.');
      }
    }

    if (data.imageCount !== lastCount){
      const $wrap = $form.find('.fmdd-image-links');
      if ($wrap.length){
        const existing = [];
        $wrap.find('input[name="image_links[]"]').each(function(){ existing.push($(this).val()); });
        $wrap.empty();
        if (data.imageCount > 0){
          for (let i=1; i<=data.imageCount; i++){
            const $label = $('<label>Image '+i+' link<span class="fmdd-req">*</span><input type="url" name="image_links[]" placeholder="https://..." data-error="Please add Image '+i+' link."></label>');
            if (existing[i-1]) $label.find('input').val(existing[i-1]);
            $wrap.append($label);
          }
        }
      }
    }

    $form.data('tplValue', data.value);
    $form.data('tplImageCount', data.imageCount);
    $form.data('tplUrl', data.url);
    $form.data('tplGuide', data.imageGuide);

    ensureColorState($form);
    ensureImageSourceState($form);
  }

  function ensureColorState($form){
    const expected = 3;
    const yes = $form.find('input[name=own_colors][value=yes]').prop('checked');
    const $note = $form.find('.fmdd-color-note');
    const templateChosen = !!($form.data('tplValue') || $form.find('.fmdd-template').val());
    if ($note.length){
      const $span = $note.find('.fmdd-color-required');
      if ($span.length) $span.text(expected);
      $note.prop('hidden', !(yes && templateChosen));
    }
    fmddToggle($form, '.fmdd-color-wrap', yes);
    if (yes){
      const current = $form.find('.fmdd-color-fields .fmdd-color-row').length;
      if (current !== expected){
        renderColorFields($form, expected);
      }
    }
    $form.find('.fmdd-color-fields input').prop('required', yes);
  }

  function ensureFontState($form){
    const yes = $form.find('input[name=own_fonts][value=yes]').prop('checked');
    fmddToggle($form, '.fmdd-font-wrap', yes);
    $form.find('.fmdd-fonts input').each(function(i){$(this).prop('required', yes && i < 2);});
    fmddUpdateFontButtons($form);
  }

  function ensureCredentialStates($form){
    const $sections = $form.find('[data-credential]');
    if (!$sections.length) return;
    $sections.each(function(){
      const $section = $(this);
      const key = $section.data('credential');
      if (!key) return;
      const $fieldsWrap = $section.find('[data-credential-fields="'+key+'"]');
      const $note = $section.find('[data-credential-recommendation="'+key+'"]');
      const $statusRadios = $form.find('input[name="'+key+'_status"]');
      const status = $statusRadios.filter('[type=hidden]').val() || ($statusRadios.length ? $form.find('input[name="'+key+'_status"]:checked').val() : 'have');
      const showFields = status === 'have';
      if ($fieldsWrap.length){
        $fieldsWrap.toggleClass('hidden', !showFields).toggleClass('shows', !!showFields);
        $fieldsWrap.find('input').each(function(){
          if (showFields){
            $(this).attr('required', 'required');
          } else {
            $(this).removeAttr('required').val('');
            if (window.fmddClearFieldError) window.fmddClearFieldError(this);
          }
        });
      }
      if ($note.length){
        const showNote = status === 'need';
        $note.toggleClass('hidden', !showNote).toggleClass('shows', !!showNote);
      }
    });
  }

  function ensureServiceRowStructure($row, index){
    $row.attr('data-index', index);
    let $heading = $row.find('.fmdd-service-heading');
    if (!$heading.length){
      $heading = $('<p class="fmdd-service-heading"></p>');
      $row.prepend($heading);
    }
    $heading.text('Item '+index);

    let $remove = $row.find('.fmdd-remove-service');
    if (!$remove.length){
      $remove = $('<button type="button" class="fmdd-remove-service" aria-label="Remove item '+index+'">Remove</button>');
      $row.append($remove);
    } else {
      $remove.attr('aria-label', 'Remove item '+index);
    }
  }

  function renumberServices($form){
    const $rows = $form.find('.fmdd-service-row');
    $rows.each(function(idx){
      ensureServiceRowStructure($(this), idx+1);
    });
    const moreThanOne = $rows.length > 1;
    $rows.find('.fmdd-remove-service')[moreThanOne ? 'show' : 'hide']();
  }

  function updateAddServiceState($form){
    const $container = $form.find('.fmdd-services');
    const max = parseInt($container.data('max'), 10) || 5;
    const count = $container.children('.fmdd-service-row').length;
    $form.find('.fmdd-add-service').prop('disabled', count >= max);
  }

  function addServiceRow($form){
    const $container = $form.find('.fmdd-services');
    if (!$container.length) return;
    const max = parseInt($container.data('max'), 10) || 5;
    const count = $container.children('.fmdd-service-row').length;
    if (count >= max) return;
    const $row = $(`
      <div class="fmdd-service-row">
        <div class="fmdd-service-fields">
          <label>Item title<span class="fmdd-req">*</span>
            <input type="text" name="service_title[]" required>
          </label>
          <label>Description<span class="fmdd-req">*</span>
            <textarea name="service_description[]" rows="2" maxlength="200" placeholder="Max 200 characters" required></textarea>
          </label>
          <label>Price
            <input type="text" name="service_price[]" placeholder="e.g., £120 / session">
          </label>
        </div>
      </div>
    `);
    $container.append($row);
    renumberServices($form);
    updateAddServiceState($form);
    return $row;
  }

  function ensureServiceRows($form){
    const $container = $form.find('.fmdd-services');
    if (!$container.length) return;
    if (!$container.children('.fmdd-service-row').length){
      addServiceRow($form);
    }
    renumberServices($form);
    updateAddServiceState($form);
  }

  function handleAssetUploads($form){
    const $toggles = $form.find('[data-asset-toggle]');
    if (!$toggles.length) return;
    $toggles.each(function(){
      const $cb = $(this);
      const slug = $cb.data('asset-toggle');
      if (!slug) return;
      const $upload = $form.find('[data-asset-upload="'+slug+'"]');
      if (!$upload.length) return;
      const requireFile = $cb.prop('checked');
      if ($cb.prop('checked')){
        $upload.removeClass('hidden').addClass('shows');
      } else {
        $upload.addClass('hidden').removeClass('shows');
      }
      const $file = $upload.find('input[type="file"]');
      if ($file.length){
        if (requireFile){
          $file.attr('required', 'required');
        } else {
          $file.removeAttr('required').val('');
          if (window.fmddClearFieldError) window.fmddClearFieldError($file[0]);
          updateFileNameDisplay($file[0]);
        }
      }
    });
  }

  function resetFileDisplays($form){
    $form.find('.fmdd-file-name').each(function(){
      const $name = $(this);
      const placeholder = $name.data('placeholder') || 'No files chosen';
      $name.empty().text(placeholder);
    });
  }

  function updateFileNameDisplay(input){
    if (!input) return;
    const $input = $(input);
    const $wrap = $input.closest('.fmdd-file');
    if (!$wrap.length) return;
    const $name = $wrap.find('.fmdd-file-name');
    if (!$name.length) return;
    const placeholder = $name.data('placeholder') || 'No files chosen';
    if (input.files && input.files.length){
      const $list = $('<ul class="fmdd-file-list"></ul>');
      Array.prototype.forEach.call(input.files, function(file){
        $('<li></li>').text(file.name).appendTo($list);
      });
      $name.empty().append($list);
    } else {
      $name.empty().text(placeholder);
    }
  }

  function ensureImageSourceState($form){
    if (!$form.find('input[name=have_images]').length) return;
    const val = $form.find('input[name=have_images]:checked').val();
    const $drive = $form.find('[name="image_drive_link"]');
    const $imageLinks = $form.find('.fmdd-image-links input[name="image_links[]"]');

    if (val === 'yes'){
      fmddToggle($form, '.fmdd-images-have', true);
      fmddToggle($form, '.fmdd-images-freepik', false);
      if ($drive.length) $drive.attr('required', 'required');
      $imageLinks.each(function(){
        this.removeAttribute('required');
      });
    } else if (val === 'freepik'){
      fmddToggle($form, '.fmdd-images-have', false);
      fmddToggle($form, '.fmdd-images-freepik', true);
      if ($drive.length){
        $drive.removeAttr('required').val('');
        if (window.fmddClearFieldError) window.fmddClearFieldError($drive[0]);
      }
      $imageLinks.each(function(){
        this.setAttribute('required', 'required');
      });
    } else if (val === 'need_help'){
      fmddToggle($form, '.fmdd-images-have', false);
      fmddToggle($form, '.fmdd-images-freepik', false);
      $imageLinks.each(function(){
        this.removeAttribute('required');
        this.value = '';
        if (window.fmddClearFieldError) window.fmddClearFieldError(this);
      });
      if ($drive.length){
        $drive.removeAttr('required');
      }
    } else {
      fmddToggle($form, '.fmdd-images-have', false);
      fmddToggle($form, '.fmdd-images-freepik', false);
      $imageLinks.each(function(){ this.removeAttribute('required'); });
      if ($drive.length) $drive.removeAttr('required');
    }
  }

  function ensureLocationMode($form){
     const $checked = $form.find('input[name=location_mode]:checked');
     const mode = $checked.length ? $checked.val() : '';
     const isOnline = mode === 'online';
     const $grid = $form.find('.fmdd-location-grid');
     if (!$grid.length) return;
    const $address = $grid.find('[name="location_address"]');
    const $state = $grid.find('[name="location_state"]');
    const $zip = $grid.find('[name="location_zip"]');
    const inputs = [$address, $state, $zip];

    if (isOnline){
      $grid.addClass('hidden');
      inputs.forEach(($input) => {
        if (!$input || !$input.length) return;
        const dom = $input[0];
        const current = $.trim(String($input.val() || ''));
        if (current) $input.data('fmddPrevValue', current);
        dom.removeAttribute('required');
        $input.val('');
        if (window.fmddClearFieldError) window.fmddClearFieldError(dom);
      });
    } else {
      $grid.removeClass('hidden');
      if ($address.length){
        $address.removeAttr('required');
        const prev = $address.data('fmddPrevValue');
        if (prev !== undefined && !$address.val()) $address.val(prev);
      }
      if ($state.length){
        $state.removeAttr('required');
        const prevState = $state.data('fmddPrevValue');
        if (prevState !== undefined && !$state.val()) $state.val(prevState);
      }
      if ($zip.length){
        const prevZip = $zip.data('fmddPrevValue');
        if (prevZip !== undefined && !$zip.val()) $zip.val(prevZip);
        $zip.attr('required', 'required');
      }
    }
  }

  function fmddInit($form){
    if (!$form.length || $form.data('fmddReady')) return;
    $form.data('fmddReady', true);
    $form.find('[data-fmdd]').removeAttr('onclick');
    if ($form[0] && $form[0].getAttribute){
      const inline = $form[0].getAttribute('onsubmit');
      if (inline) $form.data('fmcd-inline', inline);
      $form.removeAttr('onsubmit');
    }
    fmddGo($form, 1);
    ensureServiceRows($form);
    updateTemplateDetails($form);
    ensureColorState($form);
    ensureFontState($form);
    ensureCredentialStates($form);
    handleAssetUploads($form);
    ensureImageSourceState($form);
    ensureLocationMode($form);


  }

  $(function(){
    $('.fmdd-form').each(function(){ fmddInit($(this)); });
  });

  $(document).on('focus', '.fmdd-form :input', function(){
    const $form = fmddGetForm(this);
    fmddInit($form);
  });

  $(document).on('click', '.fmdd-steps button', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    fmddInit($form);
    let target = parseInt($(this).data('step'), 10) || 1;
    const current = parseInt($form.attr('data-fmdd-step'), 10) || 1;
    if (target > current){
      if (target > current + 1) target = current + 1;
      if (window.fmddValidateStep && !window.fmddValidateStep($form[0], current)) return;
    }
    fmddGo($form, target);
  });

  $(document).on('click', '.fmdd-form .fmdd-nav .next', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    fmddInit($form);
    const current = parseInt($form.attr('data-fmdd-step'), 10) || 1;
    if (window.fmddValidateStep && !window.fmddValidateStep($form[0], current)) return;
    fmddGo($form, current + 1);
  });

  $(document).on('click', '.fmdd-form .fmdd-nav .back', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    fmddInit($form);
    fmddGo($form, Math.max(1, (parseInt($form.attr('data-fmdd-step'), 10) || 1) - 1));
  });

  $(document).on('change', '.fmdd-form .fmdd-template', function(){
    const $form = fmddGetForm(this);
    fmddInit($form);
    updateTemplateDetails($form);
    ensureColorState($form);
    ensureImageSourceState($form);
  });

  $(document).on('change', '.fmdd-form input[name=own_colors]', function(){
    const $form = fmddGetForm(this);
    ensureColorState($form);
  });

  $(document).on('change', '.fmdd-form input[name=location_mode]', function(){
    ensureLocationMode(fmddGetForm(this));
  });

  $(document).on('change', '.fmdd-form input[name=own_fonts]', function(){
    const $form = fmddGetForm(this);
    ensureFontState($form);
  });

  $(document).on('change', '.fmdd-form [data-credential-status]', function(){
    const $form = fmddGetForm(this);
    ensureCredentialStates($form);
  });

  $(document).on('click', '.fmdd-form .fmdd-add-service', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    addServiceRow($form);
  });

  $(document).on('click', '.fmdd-form .fmdd-remove-service', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    $(this).closest('.fmdd-service-row').remove();
    ensureServiceRows($form);
  });

  $(document).on('click', '.fmdd-form .fmdd-add-font', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    if (fmddCountFonts($form) >= 4) return;
    $form.find('.fmdd-fonts').append('<div class="fmdd-font-row"><input type="text" name="fonts[]" placeholder="Additional font"></div>');
    fmddUpdateFontButtons($form);
  });

  $(document).on('click', '.fmdd-form .fmdd-remove-font', function(e){
    e.preventDefault();
    const $form = fmddGetForm(this);
    $(this).closest('.fmdd-font-row').remove();
    fmddUpdateFontButtons($form);
  });

  $(document).on('change', '.fmdd-form [data-asset-toggle]', function(){
    const $form = fmddGetForm(this);
    handleAssetUploads($form);
  });

  $(document).on('change', '.fmdd-form input[name=have_images]', function(){
    const $form = fmddGetForm(this);
    ensureImageSourceState($form);
  });

  $(document).on('change', '.fmdd-file-trigger input[type="file"]', function(){
    updateFileNameDisplay(this);
  });

  $(document).on('change', '.fmdd-form input[name="content_file[]"]', function(){
    const link = this.form.querySelector('[name=content_link]');
    link.required = !this.files.length;
    if (this.files.length && window.fmddClearFieldError) window.fmddClearFieldError(link);
  });

  $(document).on('submit', '.fmdd-form', function(e){
    e.preventDefault();
    const $form = $(this);
    fmddInit($form);
    const $btn = $form.find('.fmdd-submit');
    if ($btn.prop('disabled')) return;
    const $st  = $form.find('.fmdd-status');
    for (let step=1;step<=$form.find('.fmdd-step').length;step++) {
      if(window.fmddValidateStep && !window.fmddValidateStep($form[0],step)) {
        fmddGo($form,step);$st.text('Please fix the highlighted fields.');return;
      }
    }
    $btn.prop('disabled', true).text((window.FMCD && FMCD.i18n && FMCD.i18n.sending) || 'Sending...');
    const payload = new FormData($form[0]);
    payload.append('request_id', window.fmcdRequestId($form[0]));
    payload.append('type', 'discovery');
    $.ajax({
      method: 'POST',
      url: (window.FMCD && FMCD.ajax && FMCD.ajax.url) ? (FMCD.ajax.url + '/submit') : '/wp-json/fmcd/v1/submit',
      headers: (window.FMCD && FMCD.ajax && FMCD.ajax.nonce) ? {'X-WP-Nonce': FMCD.ajax.nonce} : {},
      processData: false,
      contentType: false,
      data: payload
    }).done(function(){
      $btn.prop('disabled', false).text('Submit');
      $st.text((window.FMCD && FMCD.i18n && FMCD.i18n.sent) || 'Submitted!');
      window.setTimeout(function(){ showModal($form); }, 30);
      $form[0].reset();
      fmddGo($form,1);
      $form.find('[name=content_link]').prop('required', true);
      delete $form[0].dataset.requestId;
      $form.removeData('tplValue tplImageCount tplUrl tplGuide tplColorCount');
      ensureServiceRows($form);
      updateTemplateDetails($form);
      ensureColorState($form);
      ensureFontState($form);
      ensureCredentialStates($form);
      handleAssetUploads($form);
      ensureImageSourceState($form);
      $form.find('.fmdd-location-grid input').each(function(){ $(this).removeData('fmddPrevValue'); });
      ensureLocationMode($form);
      resetFileDisplays($form);
    }).fail(function(response){
      $btn.prop('disabled', false).text('Submit');
      $st.text(response.responseJSON?.error || 'Submit failed. Your answers have been kept; please retry.');
    });
  });

})(jQuery);
