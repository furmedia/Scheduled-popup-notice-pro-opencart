<div data-furmedia-scheduled-popup="2" class="spn-root" hidden data-position="center"></div>
<style>
.spn-root{--spn-accent:#713568;--spn-bg:#fffafc;--spn-text:#2f2934;--spn-button:#e21d2f;--spn-dialog-width:520px;--spn-dialog-max-height:90vh;position:fixed;inset:0;z-index:2147483000;color:var(--spn-text);font-family:inherit;letter-spacing:0}
.spn-root[hidden]{display:none!important}
.spn-overlay{position:absolute;inset:0;background:var(--spn-overlay,#25182a);opacity:var(--spn-overlay-opacity,.42);backdrop-filter:blur(var(--spn-blur,3px));-webkit-backdrop-filter:blur(var(--spn-blur,3px))}
.spn-dialog{position:absolute;width:min(var(--spn-dialog-width),calc(100vw - 32px));max-height:min(var(--spn-dialog-max-height),calc(100vh - 32px));overflow:auto;border:1px solid rgba(90,55,90,.18);background:var(--spn-bg);box-shadow:0 22px 65px rgba(28,17,31,.35);text-align:center}
.spn-root[data-position="center"] .spn-dialog,.spn-root[data-position="middle_center"] .spn-dialog{left:50%;right:auto;top:50%;bottom:auto;transform:translate(-50%,-50%);border-radius:12px}
.spn-root[data-position="top_left"] .spn-dialog{left:24px;right:auto;top:24px;bottom:auto;transform:none}
.spn-root[data-position="top_center"] .spn-dialog{left:50%;right:auto;top:24px;bottom:auto;transform:translateX(-50%)}
.spn-root[data-position="top_right"] .spn-dialog{left:auto;right:24px;top:24px;bottom:auto;transform:none}
.spn-root[data-position="middle_left"] .spn-dialog{left:24px;right:auto;top:50%;bottom:auto;transform:translateY(-50%)}
.spn-root[data-position="middle_right"] .spn-dialog{left:auto;right:24px;top:50%;bottom:auto;transform:translateY(-50%)}
.spn-root[data-position="bottom_left"] .spn-dialog{left:24px;right:auto;top:auto;bottom:24px;transform:none}
.spn-root[data-position="bottom_center"] .spn-dialog{left:50%;right:auto;top:auto;bottom:24px;transform:translateX(-50%)}
.spn-root[data-position="bottom_right"] .spn-dialog{left:auto;right:24px;top:auto;bottom:24px;transform:none}

.spn-root[data-preset="minimal"] .spn-dialog{border-radius:0}
.spn-root[data-preset="bold"] .spn-dialog{border:5px solid var(--spn-accent)}
.spn-close{position:absolute;right:12px;top:12px;z-index:4;display:flex;align-items:center;justify-content:center;width:46px;height:46px;padding:0;border:3px solid #fff;border-radius:50%;background:#e21d2f;color:#fff;box-shadow:0 7px 18px rgba(115,12,27,.38);font:700 30px/1 Arial,sans-serif;cursor:pointer}
.spn-close:hover,.spn-close:focus{background:#bd1020;outline:3px solid rgba(255,255,255,.6);outline-offset:2px}
.spn-hero{height:145px;min-height:145px;overflow:hidden;background:#eee}
.spn-hero img{display:block;width:100%;height:100%;object-fit:cover;object-position:center}
.spn-content{display:flex;flex-direction:column;align-items:center;padding:18px 38px 28px}
.spn-title{max-width:calc(100% - 24px);margin:0 auto;padding:15px 24px;border-radius:999px;background:var(--spn-accent);color:#fff;font-size:25px;font-weight:700;line-height:1.15;text-transform:uppercase;box-shadow:0 9px 22px rgba(54,28,57,.22)}
.spn-message{margin:22px 0 0;color:var(--spn-text);font-size:26px;font-weight:700;line-height:1.25;text-transform:uppercase}
.spn-divider{width:78%;height:2px;margin:14px 0;background:linear-gradient(90deg,transparent,var(--spn-accent),transparent);opacity:.4}
.spn-submessage{margin:0;color:var(--spn-text);font-size:21px;font-weight:600;line-height:1.35}
.spn-countdown-wrap{margin-top:16px}
.spn-countdown-label{display:block;margin-bottom:7px;font-size:14px;font-weight:700;text-transform:uppercase}
.spn-countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:7px}
.spn-countdown-part{min-width:66px;padding:8px 5px;border:1px solid color-mix(in srgb,var(--spn-accent) 28%,transparent);background:color-mix(in srgb,var(--spn-accent) 8%,transparent)}
.spn-countdown-part strong{display:block;font-size:22px}
.spn-countdown-part small{display:block;font-size:10px;text-transform:uppercase}
.spn-cta{display:inline-flex;align-items:center;justify-content:center;min-height:44px;margin-top:17px;padding:10px 22px;border:0;border-radius:4px;background:var(--spn-button);color:#fff!important;font-size:16px;font-weight:700;text-decoration:none!important}
.spn-cta:hover,.spn-cta:focus{filter:brightness(.9);outline:3px solid color-mix(in srgb,var(--spn-button) 30%,transparent)}
.spn-thanks{align-self:stretch;margin:20px -38px -28px;padding:15px 24px;background:var(--spn-accent);color:#fff;font-size:20px;font-style:italic;line-height:1.3}
.spn-progress{position:absolute;left:16px;top:16px;z-index:3;padding:4px 8px;border-radius:20px;background:rgba(255,255,255,.9);color:#333;font-size:11px;font-weight:700}
.spn-root[data-responsive-profile="mobile"] .spn-dialog{width:min(var(--spn-dialog-width),calc(100vw - 20px));max-height:min(var(--spn-dialog-max-height),calc(100vh - 20px))}.spn-root[data-responsive-profile="mobile"] .spn-close{right:9px;top:9px;width:42px;height:42px;font-size:27px}.spn-root[data-responsive-profile="mobile"] .spn-hero{height:112px;min-height:112px}.spn-root[data-responsive-profile="mobile"] .spn-content{padding:14px 18px 22px}.spn-root[data-responsive-profile="mobile"] .spn-title{margin-top:0;padding:11px 18px;font-size:19px}.spn-root[data-responsive-profile="mobile"] .spn-message{margin-top:17px;font-size:20px}.spn-root[data-responsive-profile="mobile"] .spn-submessage{font-size:17px}.spn-root[data-responsive-profile="mobile"] .spn-thanks{margin:16px -18px -22px;padding:13px 16px;font-size:17px}.spn-root[data-responsive-profile="mobile"] .spn-countdown-part{min-width:54px}.spn-root[data-responsive-profile="mobile"] .spn-countdown-part strong{font-size:18px}.spn-root[data-responsive-profile="mobile"] .spn-cta{width:100%}
@media(max-height:620px){.spn-hero{height:82px;min-height:82px}.spn-dialog{max-height:calc(100vh - 12px)}.spn-message{font-size:19px}.spn-content{padding-bottom:18px}.spn-thanks{margin-bottom:-18px}}
@media(prefers-reduced-motion:no-preference){.spn-dialog{animation:spn-enter .22s ease-out}.spn-overlay{animation:spn-fade .18s ease-out}@keyframes spn-enter{from{opacity:0;filter:blur(2px)}to{opacity:1;filter:blur(0)}}@keyframes spn-fade{from{opacity:0}}}
</style>
<script>
(function () {
  'use strict';

  function decode(value) {
    try { return JSON.parse(decodeURIComponent(escape(atob(value)))); } catch (error) { return JSON.parse(atob(value)); }
  }
  function toInt(value, fallback) {
    var parsed = parseInt(value, 10);
    return isNaN(parsed) ? fallback : parsed;
  }
  function clampInt(value, fallback, min, max) {
    return Math.max(min, Math.min(max, toInt(value, fallback)));
  }
  function currentDevice() {
    var ua = String(navigator.userAgent || '').toLowerCase();
    var touchIpad = String(navigator.platform || '') === 'MacIntel' && Number(navigator.maxTouchPoints || 0) > 1;
    if (touchIpad || /ipad|tablet|kindle|silk|playbook/.test(ua) || (/android/.test(ua) && !/mobile/.test(ua))) {
      return 'tablet';
    }
    if (/mobile|iphone|ipod|android|windows phone|opera mini|blackberry/.test(ua)) {
      return 'mobile';
    }
    var width = window.innerWidth || document.documentElement.clientWidth || 1280;
    if (width <= 767) return 'mobile';
    if (width <= 1024) return 'tablet';
    return 'desktop';
  }
  function currentProfile(campaign) {
    var ua = String(navigator.userAgent || '').toLowerCase();
    var width = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    var mobileMax = clampInt(campaign.breakpoint_mobile, 767, 320, 900);
    var tabletMax = Math.max(mobileMax + 1, clampInt(campaign.breakpoint_tablet, 1024, 600, 1400));
    var laptopMax = Math.max(tabletMax + 1, clampInt(campaign.breakpoint_laptop, 1440, 900, 2560));
    if (/ipad|tablet|playbook|silk|(android(?!.*mobile))/i.test(ua)) return 'tablet';
    if (/mobi|iphone|ipod|android|blackberry|iemobile|opera mini/i.test(ua)) return 'mobile';
    if (width <= mobileMax) return 'mobile';
    if (width <= tabletMax) return 'tablet';
    if (width <= laptopMax) return 'laptop';
    return 'desktop';
  }
  function targetDevice(profile) {
    return profile === 'laptop' ? 'desktop' : profile;
  }
  function node(tag, className, text) {
    var el = document.createElement(tag);
    if (className) el.className = className;
    if (text != null) el.textContent = text;
    return el;
  }
  function campaignKey(campaign) {
    return campaign.id + '_' + campaign.occurrence_key;
  }
  function getState(campaign) {
    var key = campaignKey(campaign);
    if (!states[key]) {
      states[key] = {
        campaign: campaign,
        key: key,
        shown: false,
        queued: false,
        listeners: [],
        closedByTimeout: false
      };
    }
    return states[key];
  }
  function track(campaign, type) {
    var payload = 'campaign_id=' + encodeURIComponent(campaign.id) +
      '&occurrence_key=' + encodeURIComponent(campaign.occurrence_key) +
      '&event_type=' + encodeURIComponent(type);
    if (navigator.sendBeacon) {
      var blob = new Blob([payload], { type: 'application/x-www-form-urlencoded;charset=UTF-8' });
      navigator.sendBeacon(trackUrl, blob);
      return;
    }
    fetch(trackUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: payload,
      credentials: 'same-origin',
      keepalive: true
    }).catch(function () {});
  }
  function formatTime(value, fallback) {
    return value < 10 ? '0' + value : String(value);
  }
  function updateCountdown(campaign, container) {
    var left = Math.max(0, Date.parse(campaign.countdown_end) - Date.now());
    if (left <= 0) {
      return false;
    }
    var values = [
      Math.floor(left / 86400000),
      Math.floor((left % 86400000) / 3600000),
      Math.floor((left % 3600000) / 60000),
      Math.floor((left % 60000) / 1000)
    ];
    var names = [labels.days, labels.hours, labels.minutes, labels.seconds];
    container.innerHTML = '';
    values.forEach(function(value, index) {
      var part = node('span', 'spn-countdown-part');
      part.appendChild(node('strong', '', formatTime(value)));
      part.appendChild(node('small', '', names[index]));
      container.appendChild(part);
    });
    return true;
  }
  function scheduleQueue(campaign) {
    var state = getState(campaign);
    if (state.shown || state.queued) {
      return;
    }
    state.queued = true;
    queue.push(campaign);
    processQueue();
  }
  function closeCampaign(campaign, remember, fromCountdown) {
    clearInterval(countdownTimer);
    clearTimeout(autoCloseTimer);

    if (remember) {
      try { sessionStorage.setItem('spn_closed_' + campaignKey(campaign), '1'); } catch (error) {}
      track(campaign, 'close');
    } else if (fromCountdown) {
      track(campaign, 'close');
    }
    activeCampaign = null;
    root.innerHTML = '';
    root.hidden = true;
    document.removeEventListener('keydown', onEscape);
    if (lastFocus && lastFocus.focus) {
      try { lastFocus.focus(); } catch (error) {}
    }
    lastFocus = null;
    processQueue();
  }
  function onEscape(event) {
    if (event.key !== 'Escape' || root.hidden || !activeCampaign) return;
    closeCampaign(activeCampaign, true, false);
  }
  function show(campaign) {
    if (!campaign) return;
    clearInterval(countdownTimer);
    clearTimeout(autoCloseTimer);
    activeCampaign = campaign;
    getState(campaign).queued = false;
    getState(campaign).shown = true;

    root.innerHTML = '';
    var responsiveProfile = currentProfile(campaign);
    var widthDefaults = {desktop:520,laptop:520,tablet:480,mobile:360};
    var heightDefaults = {desktop:90,laptop:90,tablet:92,mobile:94};
    root.dataset.position = campaign.position || 'center';
    root.dataset.preset = campaign.preset;
    root.dataset.responsiveProfile = responsiveProfile;
    root.style.setProperty('--spn-dialog-width', clampInt(campaign['popup_width_' + responsiveProfile], widthDefaults[responsiveProfile], 240, 1600) + 'px');
    root.style.setProperty('--spn-dialog-max-height', clampInt(campaign['popup_height_' + responsiveProfile], heightDefaults[responsiveProfile], 45, 98) + 'vh');
    root.style.setProperty('--spn-accent', campaign.accent_color);
    root.style.setProperty('--spn-bg', campaign.background_color);
    root.style.setProperty('--spn-text', campaign.text_color);
    root.style.setProperty('--spn-button', campaign.button_color);
    root.style.setProperty('--spn-overlay', campaign.overlay_color);
    root.style.setProperty('--spn-overlay-opacity', String(campaign.overlay_opacity / 100));
    root.style.setProperty('--spn-blur', String(campaign.blur) + 'px');

    var overlay = node('div', 'spn-overlay');
    var dialog = node('div', 'spn-dialog');
    var close = node('button', 'spn-close', '×');
    var hero = node('div', 'spn-hero');
    var image = document.createElement('img');
    var content = node('div', 'spn-content');
    var title = node('h2', 'spn-title', campaign.title);
    var message = node('p', 'spn-message', campaign.message);
    var divider = node('div', 'spn-divider');
    var submessage = node('p', 'spn-submessage', campaign.submessage);
    var thanks = node('div', 'spn-thanks', campaign.thanks);
    var countdown = null;

    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-labelledby', 'spn-title-' + campaign.id);
    title.id = 'spn-title-' + campaign.id;
    close.type = 'button';
    close.setAttribute('aria-label', labels.close);

    image.src = campaign.image;
    image.alt = '';
    image.decoding = 'async';
    hero.appendChild(image);

    dialog.appendChild(close);
    dialog.appendChild(hero);
    content.appendChild(title);
    content.appendChild(message);
    content.appendChild(divider);
    content.appendChild(submessage);

    if (campaign.countdown) {
      countdown = node('div', 'spn-countdown-wrap');
      var countdownLabel = node('span', 'spn-countdown-label', campaign.countdown_label);
      var countdownGrid = node('div', 'spn-countdown');
      countdown.appendChild(countdownLabel);
      countdown.appendChild(countdownGrid);
      content.appendChild(countdown);
      updateCountdown(campaign, countdownGrid);
      countdownTimer = setInterval(function () {
        if (!updateCountdown(campaign, countdownGrid)) {
          closeCampaign(campaign, false, true);
        }
      }, 1000);
    }

    if (campaign.button_url && campaign.button_text) {
      var cta = node('a', 'spn-cta', campaign.button_text);
      cta.href = campaign.button_url;
      cta.target = campaign.button_target;
      cta.rel = campaign.button_target === '_blank' ? 'noopener noreferrer' : '';
      cta.addEventListener('click', function () { track(campaign, 'click'); });
      content.appendChild(cta);
    }
    content.appendChild(thanks);
    dialog.appendChild(content);

    if (campaigns.length > 1) {
      var progress = node('span', 'spn-progress');
      progress.textContent = (completedCount) + ' / ' + (campaigns.length);
      dialog.appendChild(progress);
    }

    root.appendChild(overlay);
    root.appendChild(dialog);

    if (lastFocus === null) {
      lastFocus = document.activeElement;
    }
    root.hidden = false;
    close.focus();
    closeCampaignRef = function () { closeCampaign(campaign, true, false); };
    close.addEventListener('click', closeCampaignRef);
    document.addEventListener('keydown', onEscape);

    var autoClose = toInt(campaign.auto_close_seconds, 0);
    if (autoClose > 0) {
      autoCloseTimer = setTimeout(function () {
        closeCampaign(campaign, false, false);
      }, autoClose * 1000);
    }
    track(campaign, 'impression');
  }
  function processQueue() {
    if (activeCampaign || !queue.length) {
      if (!activeCampaign && !queue.length && root) {
        root.hidden = true;
        root.innerHTML = '';
        if (lastFocus && lastFocus.focus) {
          try { lastFocus.focus(); } catch (error) {}
        }
        lastFocus = null;
      }
      return;
    }
    var next = queue.shift();
    show(next);
    completedCount++;
  }
  function attachScrollTrigger(state) {
    scrollStates.push(state);
    if (!scrollAttached) {
      scrollAttached = true;
      var check = function () { checkScrollCampaigns(); };
      window.addEventListener('scroll', check, { passive: true });
      window.addEventListener('resize', check, { passive: true });
    }
    checkScrollCampaigns();
  }
  function checkScrollCampaigns() {
    var html = document.documentElement;
    var body = document.body;
    var docHeight = Math.max(html.scrollHeight, body.scrollHeight, html.offsetHeight, body.offsetHeight, window.innerHeight || 0);
    var maxOffset = Math.max(1, docHeight - (window.innerHeight || 0));
    var offset = (window.pageYOffset || html.scrollTop || body.scrollTop || 0);
    var percent = (offset / maxOffset) * 100;
    scrollStates.slice().forEach(function (state) {
      if (!state || state.shown || state.queued) {
        return;
      }
      var threshold = Math.max(1, Math.min(100, toInt(state.campaign.trigger_scroll_depth, 55)));
      if (percent >= threshold) {
        queueCampaign(state);
      }
    });
  }
  function queueCampaign(stateOrCampaign) {
    var campaign = stateOrCampaign.campaign ? stateOrCampaign.campaign : stateOrCampaign;
    var state = getState(campaign);
    if (state.shown || state.queued) {
      return;
    }
    state.queued = true;
    queue.push(campaign);
    processQueue();
  }
  function resetInactivity(state) {
    if (!state || state.shown || state.queued) {
      return;
    }
    if (state.inactivityTimeout) clearTimeout(state.inactivityTimeout);
    state.inactivityTimeout = setTimeout(function () { queueCampaign(state); }, state.inactivityDelay);
  }
  function checkInactivity() {
    if (!inactivityCampaigns.length) {
      return;
    }
    inactivityCampaigns.forEach(function (state) {
      if (state.shown || state.queued) {
        return;
      }
      resetInactivity(state);
    });
  }
  function attachInactivity(state) {
    state.inactivityDelay = Math.max(15, toInt(state.campaign.trigger_delay, 15)) * 1000;
    inactivityCampaigns.push(state);
    if (!inactivityAttached) {
      inactivityAttached = true;
      ['mousemove', 'keydown', 'touchstart', 'scroll', 'click'].forEach(function (eventName) {
        document.addEventListener(eventName, function () { checkInactivity(); }, { passive: true });
      });
    }
    resetInactivity(state);
  }
  function attachClick(state) {
    var campaign = state.campaign;
    var selector = String(campaign.trigger_selector || '').trim();
    var elements = selector ? document.querySelectorAll(selector) : [];
    var handler = function (event) {
      if (!selector) {
        queueCampaign(state);
        return;
      }
      var current = event.target;
      while (current && current !== document) {
        if (current.matches && current.matches(selector)) {
          queueCampaign(state);
          return;
        }
        current = current.parentElement;
      }
    };
    if (!elements.length) {
      elements = [document];
    }
    Array.prototype.forEach.call(elements, function (element) {
      element.addEventListener('click', handler, { passive: true });
      state.listeners.push(function () { element.removeEventListener('click', handler); });
    });
  }
  function attachExit(state) {
    var campaign = state.campaign;
    var handler = function (event) {
      if (state.shown || state.queued) return;
      if (event.clientY <= 0 && !event.relatedTarget) {
        queueCampaign(state);
      }
    };
    document.addEventListener('mouseout', handler);
    state.listeners.push(function () { document.removeEventListener('mouseout', handler); });
  }
  function detectAdBlock() {
    var bait = document.createElement('div');
    bait.className = 'adsbox ad adsbox ad-banner';
    bait.style.cssText = 'position: absolute !important; width: 1px !important; height: 1px !important; left: -10000px !important; top: -10000px !important; overflow: hidden !important;';
    try {
      document.body.appendChild(bait);
      var detected = bait.offsetHeight === 0 || bait.offsetWidth === 0 || getComputedStyle(bait).display === 'none' || getComputedStyle(bait).visibility === 'hidden';
      document.body.removeChild(bait);
      return detected;
    } catch (error) {
      try { document.body.removeChild(bait); } catch (second) {}
      return false;
    }
  }
  function attachAdblock(state) {
    adblockCampaigns.push(state);
    if (adblockAttached) {
      return;
    }
    adblockAttached = true;
    adblockCheck = function () {
      var blocked = detectAdBlock();
      if (!blocked) {
        return;
      }
      adblockCampaigns.slice().forEach(function (entry) {
        if (!entry.shown && !entry.queued) {
          queueCampaign(entry);
        }
      });
      adblockCampaigns.length = 0;
      if (adblockTimer) {
        clearInterval(adblockTimer);
      }
    };
    adblockTimer = setInterval(adblockCheck, 900);
  }
  function armCampaign(campaign) {
    var state = getState(campaign);
    if (state.shown) return;
    switch (campaign.trigger) {
      case 'on_click':
        attachClick(state);
        break;
      case 'on_scroll':
        attachScrollTrigger(state);
        break;
      case 'on_exit':
        attachExit(state);
        break;
      case 'on_inactivity':
        attachInactivity(state);
        break;
      case 'on_adblock':
        attachAdblock(state);
        break;
      case 'on_delay':
        setTimeout(function () {
          queueCampaign(state);
        }, Math.max(0, toInt(campaign.trigger_delay, 0)) * 1000);
        break;
      case 'on_load':
      default:
        setTimeout(function () {
          queueCampaign(state);
        }, Math.max(0, toInt(campaign.trigger_delay, 0)) * 1000);
        break;
    }
  }

  var root = document.querySelector('[data-furmedia-scheduled-popup=\"2\"]');
  if (!root) return;
  var campaigns = decode('<?php echo $campaigns_b64; ?>');
  var trackUrl = atob('<?php echo $track_url_b64; ?>');
  var labels = decode('<?php echo $labels_b64; ?>');
  var states = {};
  var queue = [];
  var completedCount = 1;
  var activeCampaign = null;
  var lastFocus = null;
  var countdownTimer = null;
  var autoCloseTimer = null;
  var closeCampaignRef = null;
  var scrollStates = [];
  var scrollAttached = false;
  var inactivityCampaigns = [];
  var inactivityAttached = false;
  var adblockCampaigns = [];
  var adblockAttached = false;
  var adblockTimer = null;
  var adblockCheck = null;

  campaigns = campaigns.filter(function (campaign) {
    var device = targetDevice(currentProfile(campaign));
    var allowedDevices = Array.isArray(campaign.devices) && campaign.devices.length ? campaign.devices : ['desktop', 'tablet', 'mobile'];
    if (allowedDevices.indexOf(device) === -1) {
      return false;
    }
    try {
      return sessionStorage.getItem('spn_closed_' + campaign.id + '_' + campaign.occurrence_key) !== '1';
    } catch (error) {
      return true;
    }
  });
  if (!campaigns.length || typeof trackUrl === 'undefined') {
    root.hidden = true;
    return;
  }
  if (document.body && root.parentNode !== document.body) {
    document.body.appendChild(root);
  }
  campaigns.forEach(armCampaign);
  processQueue();
})();
</script>
