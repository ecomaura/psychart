/* PSYCHART.CO shell: menu drawer, search overlay, bag drawer, newsletter. No dependencies. */
(function () {
  'use strict';
  var C = window.PSYCHART || {};
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  var panels = { menu: $('#psy-menu'), search: $('#psy-search'), bag: $('#psy-bag'), filters: $('#psy-filters') };
  var scrim = $('.psy-scrim');
  var current = null, lastFocus = null;

  /* ---------- open / close ---------- */
  function open(name) {
    var el = panels[name];
    if (!el) return;
    if (current && current !== name) close(true);
    lastFocus = document.activeElement;
    current = name;
    el.hidden = false;
    el.setAttribute('aria-hidden', 'false');
    if (name !== 'search') { scrim.hidden = false; }
    document.body.classList.add('psy-lock');
    $$('[data-psy-open="' + name + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        el.classList.add('is-open');
        if (name !== 'search') scrim.classList.add('is-open');
        var f = name === 'search' ? $('#psy-search-input') : $('button, a', el);
        if (f) f.focus({ preventScroll: true });
      });
    });
    if (name === 'bag') refreshBag();
  }

  function close(instant) {
    if (!current) return;
    var name = current, el = panels[name];
    current = null;
    el.classList.remove('is-open');
    scrim.classList.remove('is-open');
    el.setAttribute('aria-hidden', 'true');
    $$('[data-psy-open="' + name + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    var done = function () {
      if (current === name) return;
      el.hidden = true;
      if (!current) { scrim.hidden = true; document.body.classList.remove('psy-lock'); }
    };
    if (instant) { done(); } else { setTimeout(done, 360); }
    if (lastFocus && !instant) lastFocus.focus({ preventScroll: true });
  }

  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-psy-open]');
    if (o) { e.preventDefault(); open(o.getAttribute('data-psy-open')); return; }
    if (e.target.closest('[data-psy-close]')) { e.preventDefault(); close(); }
  });

  document.addEventListener('keydown', function (e) {
    if (!current) return;
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'Tab') { // focus trap
      var f = $$('a[href],button:not([disabled]),input,select', panels[current]).filter(function (n) { return n.offsetParent !== null; });
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  /* ---------- theme toggle ---------- */
  var root = document.documentElement;
  var themeBtns = $$('[data-psy-theme-toggle]');
  var KEY = 'psychart-theme';
  function currentTheme() { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; }
  function paintTheme() {
    var dark = currentTheme() === 'dark';
    var i18n = C.i18n || {};
    themeBtns.forEach(function (b) {
      b.setAttribute('aria-pressed', dark ? 'true' : 'false');
      b.setAttribute('aria-label', dark ? (i18n.darkOn || 'Switch to light theme') : (i18n.darkOff || 'Switch to dark theme'));
    });
    var m = $('meta[name=theme-color]');
    if (m) m.setAttribute('content', dark ? '#0e0e0e' : '#fafafa');
  }
  function setTheme(t, save) {
    root.classList.add('psy-theme-anim');
    root.setAttribute('data-theme', t);
    if (save) { try { localStorage.setItem(KEY, t); } catch (err) {} }
    paintTheme();
    setTimeout(function () { root.classList.remove('psy-theme-anim'); }, 350);
  }
  themeBtns.forEach(function (b) {
    b.addEventListener('click', function () { setTheme(currentTheme() === 'dark' ? 'light' : 'dark', true); });
  });
  paintTheme();
  // Follow the system setting until the visitor picks a theme themselves.
  if (window.matchMedia) {
    var mq = window.matchMedia('(prefers-color-scheme: dark)');
    var onSys = function (e) {
      var saved = null;
      try { saved = localStorage.getItem(KEY); } catch (err) {}
      if (saved !== 'light' && saved !== 'dark') setTheme(e.matches ? 'dark' : 'light', false);
    };
    if (mq.addEventListener) mq.addEventListener('change', onSys); else if (mq.addListener) mq.addListener(onSys);
  }

  /* ---------- bag ---------- */
  var bagInner = $('.psy-bag-inner');
  function post(action, data) {
    var body = new URLSearchParams(Object.assign({ action: action }, data || {}));
    return fetch(C.ajax, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); });
  }
  function applyBag(res) {
    if (!res || !res.success) return;
    bagInner.innerHTML = res.data.html;
    $$('.psy-bag-count').forEach(function (n) { n.textContent = res.data.count; n.setAttribute('data-count', res.data.count); });
    bagInner.classList.remove('is-loading');
  }
  function refreshBag() {
    bagInner.classList.add('is-loading');
    return post('psychart_cart').then(applyBag).catch(function () { bagInner.classList.remove('is-loading'); });
  }
  bagInner.addEventListener('click', function (e) {
    var li = e.target.closest('.psy-bag__item');
    if (!li) return;
    var key = li.getAttribute('data-key');
    var q = e.target.closest('[data-psy-qty]');
    var r = e.target.closest('[data-psy-remove]');
    if (!q && !r) return;
    var qty = 0;
    if (q) qty = Math.max(0, parseInt($('.psy-qty span', li).textContent, 10) + parseInt(q.getAttribute('data-psy-qty'), 10));
    bagInner.classList.add('is-loading');
    post('psychart_cart_update', { key: key, qty: qty }).then(applyBag).catch(function () { bagInner.classList.remove('is-loading'); });
  });

  // Public hook for the product page (Phase 4) and any add-to-cart code.
  window.Psychart = window.Psychart || {};
  window.Psychart.bag = {
    open: function () { open('bag'); },
    close: close,
    refresh: refreshBag,
    // Call after a successful add-to-cart: refreshes the bag and opens the drawer.
    added: function () { return refreshBag().then(function () { open('bag'); }); }
  };
  document.addEventListener('psychart:added', function () { window.Psychart.bag.added(); });
  // WooCommerce's own AJAX add-to-cart buttons (archives) fire this jQuery event.
  if (window.jQuery) { window.jQuery(document.body).on('added_to_cart', function () { window.Psychart.bag.added(); }); }

  /* ---------- live search ---------- */
  var input = $('#psy-search-input');
  var results = $('[data-psy-search-results]');
  var status = $('.psy-search__status');
  var msg = $('[data-psy-search-msg]');
  var more = $('[data-psy-search-more]');
  var timer, reqId = 0;

  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function setStatus(text) { msg.textContent = text; status.classList.remove('is-hidden'); results.innerHTML = ''; more.hidden = true; }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    if (q.length < 2) { setStatus(C.i18n.typeMore); return; }
    timer = setTimeout(function () {
      var id = ++reqId;
      fetch(C.ajax + '?action=psychart_search&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (id !== reqId || !res.success) return;
          var items = res.data.items;
          if (!items.length) { setStatus(C.i18n.noResults); return; }
          status.classList.add('is-hidden');
          results.innerHTML = items.map(function (i) {
            return '<li><a href="' + esc(i.url) + '"><img src="' + esc(i.img) + '" alt="" loading="lazy"><span class="t">' + esc(i.title) + '</span><span class="p">' + esc(i.price) + '</span></a></li>';
          }).join('');
          more.href = res.data.more;
          more.hidden = res.data.total <= items.length;
        })
        .catch(function () { setStatus(C.i18n.error); });
    }, 220);
  });

  /* ---------- shop: sort + filters ---------- */
  var sort = $('[data-psy-sort]');
  if (sort) sort.addEventListener('change', function () { sort.form.submit(); });

  if (panels.filters) {
    // Filter options are links; reopen the drawer after the page reloads so several can be picked in a row.
    panels.filters.addEventListener('click', function (e) {
      if (e.target.closest('a[href].psy-chip, .psy-filter__list a, form button[type=submit]')) {
        try { sessionStorage.setItem('psyFilters', '1'); } catch (err) {}
      }
    });
    try {
      if (sessionStorage.getItem('psyFilters')) { sessionStorage.removeItem('psyFilters'); open('filters'); }
    } catch (err) {}
  }

  /* ---------- newsletter ---------- */
  $$('[data-psy-news]').forEach(function (form) {
    var out = $('[data-psy-news-msg]', form);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      out.className = 'psy-news__msg';
      out.textContent = '';
      var data = new FormData(form);
      var body = new URLSearchParams({ action: 'psychart_subscribe', nonce: C.nonce, email: data.get('email') || '', website: data.get('website') || '' });
      fetch(C.ajax, { method: 'POST', credentials: 'same-origin', body: body })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.success) { form.reset(); out.textContent = (res.data && res.data.message) || C.i18n.joined; }
          else { out.classList.add('is-error'); out.textContent = (res.data && res.data.message) || C.i18n.error; }
        })
        .catch(function () { out.classList.add('is-error'); out.textContent = C.i18n.error; });
    });
  });
})();
