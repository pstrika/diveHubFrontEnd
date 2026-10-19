/*
 * Marine life tagging on the dive page (Pablo, 2026-10-17): the dive's
 * "Marine life" card, and species chips in the photo viewer
 * (window.dhPhotoSpecies, called by dive-photos.js). Tagging a photo also
 * tags the dive. The first tag ever asks once whether to share sightings
 * (species, site, date - never the dive) with the community.
 */
(function () {
  var cfg = window.dhDiveSpecies;
  if (!cfg) return;

  function send(method, url, body) {
    return fetch(url, {
      method: method, credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (d) {
        if (!r.ok) throw new Error(d.message || 'Something went wrong');
        return d;
      });
    });
  }

  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

  // Asked once, before the first tag is saved.
  function askShareOnce() {
    if (cfg.shareSightings !== null) return Promise.resolve();
    return new Promise(function (resolve) {
      var box = document.createElement('div');
      box.className = 'dh-species-consent';
      box.setAttribute('role', 'dialog');
      box.innerHTML = '<div class="dh-species-consent-card">'
        + '<h4>Share your sightings?</h4>'
        + '<p>Let other divers see what was spotted where: the species, the site and the date. Your dives, photos and notes stay private either way. You can change this any time under Marine life.</p>'
        + '<div class="dh-species-consent-actions"><button type="button" class="dh-btn dh-btn-primary" data-v="1">Share sightings</button>'
        + '<button type="button" class="dh-btn dh-btn-ghost-dark" data-v="0">Keep them private</button></div></div>';
      document.body.appendChild(box);
      box.addEventListener('click', function (e) {
        var b = e.target.closest('[data-v]');
        if (!b) return;
        var share = b.getAttribute('data-v') === '1';
        cfg.shareSightings = share;
        send('POST', cfg.shareUrl, { share: share }).catch(function () {});
        box.remove();
        resolve();
      });
    });
  }

  // A type-ahead over the catalog + the diver's own species.
  function picker(host, placeholder, exclude, onPick) {
    host.innerHTML = '<div class="dh-logbook-search"><span class="material-icons-round" aria-hidden="true">add</span>'
      + '<input type="search" placeholder="' + esc(placeholder) + '" aria-label="' + esc(placeholder) + '" autocomplete="off"></div>'
      + '<div class="dh-species-results" hidden></div>';
    var input = host.querySelector('input');
    var list = host.querySelector('.dh-species-results');
    function show() {
      var terms = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
      if (!terms.length) { list.hidden = true; return; }
      var skip = exclude();
      var hits = cfg.all.filter(function (s) {
        if (skip[s.uuid]) return false;
        var t = (s.name + ' ' + (s.sci || '') + ' ' + (s.alt || '')).toLowerCase();
        return terms.every(function (w) { return t.indexOf(w) !== -1; });
      }).slice(0, 8);
      list.innerHTML = hits.map(function (s) {
        return '<button type="button" data-uuid="' + s.uuid + '"><strong>' + esc(s.name) + '</strong> <em>' + esc(s.sci || '') + '</em><span>' + esc(s.cat) + '</span></button>';
      }).join('') + '<a href="' + cfg.newUrl + '" class="dh-species-results-new">Not here? Add "' + esc(input.value.trim()) + '" as your own species</a>';
      list.hidden = false;
    }
    input.addEventListener('input', show);
    input.addEventListener('focus', show);
    document.addEventListener('click', function (e) { if (!host.contains(e.target)) list.hidden = true; });
    list.addEventListener('click', function (e) {
      var b = e.target.closest('[data-uuid]');
      if (!b) return;
      input.value = ''; list.hidden = true;
      onPick(b.getAttribute('data-uuid'));
    });
  }

  // Dive card.
  var chips = document.getElementById('diveSpeciesChips');
  var tagged = cfg.tagged.slice();
  function renderDive() {
    if (!chips) return;
    chips.innerHTML = tagged.length ? '' : '<p class="text-sm text-secondary mb-2">Nothing tagged yet. What did you see?</p>';
    tagged.forEach(function (s) {
      var c = document.createElement('span');
      c.className = 'dh-species-chip';
      c.innerHTML = '<a href="' + s.url + '">' + esc(s.name) + '</a>'
        + '<button type="button" class="dh-species-chip-count" title="How many?">' + (s.count ? '&times;' + s.count : '#') + '</button>'
        + '<button type="button" class="dh-species-chip-x" aria-label="Remove ' + esc(s.name) + '"><span class="material-icons-round" aria-hidden="true">close</span></button>';
      c.querySelector('.dh-species-chip-x').addEventListener('click', function () {
        send('DELETE', cfg.untagUrl + s.uuid).then(function (d) { tagged = d.species; renderDive(); }).catch(function (e) { alert(e.message); });
      });
      c.querySelector('.dh-species-chip-count').addEventListener('click', function () {
        var n = prompt('How many ' + s.name + ' did you see?', s.count || '');
        if (n === null) return;
        n = parseInt(n, 10);
        send('POST', cfg.tagUrl, { species: s.uuid, count: n > 0 ? n : null }).then(function (d) { tagged = d.species; renderDive(); });
      });
      chips.appendChild(c);
    });
  }
  function taggedSet() { var m = {}; tagged.forEach(function (s) { m[s.uuid] = 1; }); return m; }
  var divePicker = document.getElementById('diveSpeciesPicker');
  if (divePicker) {
    picker(divePicker, 'Add a species', taggedSet, function (uuid) {
      askShareOnce().then(function () {
        return send('POST', cfg.tagUrl, { species: uuid });
      }).then(function (d) { tagged = d.species; renderDive(); }).catch(function (e) { alert(e.message); });
    });
  }
  renderDive();

  // Photo viewer.
  window.dhPhotoSpecies = function (photo, onChange) {
    var host = document.getElementById('dhPhotoViewerSpecies');
    if (!host) return;
    host.innerHTML = '<div class="dh-species-chips"></div>'
      + (cfg.identify ? '<div class="dh-whats-this"><button type="button" class="dh-btn dh-btn-ghost-dark" data-identify><span class="material-icons-round" aria-hidden="true">auto_awesome</span>What\'s this?</button><div class="dh-whats-this-out" aria-live="polite"></div></div>' : '')
      + '<div class="dh-species-picker"></div>';
    var box = host.querySelector('.dh-species-chips');

    // "What's this?" (2026-10-18): AI suggestions from our catalog; the
    // diver taps one to tag it - nothing is tagged on its own. The first
    // time, the server asks for consent (409) before a photo leaves.
    function identify(consent) {
      return fetch(cfg.photoUrl + photo.uuid + '/identify', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(consent ? { consent: true } : {}),
      }).then(function (r) {
        return r.json().catch(function () { return {}; }).then(function (d) {
          if (r.status === 409 && d.consent_required) return askAiConsent(d.message).then(function (ok) {
            if (!ok) throw new Error('Not sent - your photo stays private.');
            return identify(true);
          });
          if (!r.ok) throw new Error(d.message || 'Something went wrong');
          return d;
        });
      });
    }
    function askAiConsent(message) {
      return new Promise(function (resolve) {
        var box = document.createElement('div');
        box.className = 'dh-species-consent';
        box.setAttribute('role', 'dialog');
        box.innerHTML = '<div class="dh-species-consent-card"><h4>Identify with AI?</h4><p>' + esc(message) + '</p>'
          + '<p class="text-xs">Anthropic doesn\'t use your photo to train its models. You\'ll only be asked once.</p>'
          + '<div class="dh-species-consent-actions"><button type="button" class="dh-btn dh-btn-primary" data-v="1">Send photo</button>'
          + '<button type="button" class="dh-btn dh-btn-ghost-dark" data-v="0">Not now</button></div></div>';
        document.body.appendChild(box);
        box.addEventListener('click', function (e) {
          var b = e.target.closest('[data-v]'); if (!b) return;
          box.remove(); resolve(b.getAttribute('data-v') === '1');
        });
      });
    }
    var idBtn = host.querySelector('[data-identify]');
    if (idBtn) {
      var out = host.querySelector('.dh-whats-this-out');
      var CONF = { high: 'Likely', medium: 'Possibly', low: 'Maybe' };
      idBtn.addEventListener('click', function () {
        idBtn.disabled = true;
        out.innerHTML = '<span class="text-sm text-secondary">Looking at the photo...</span>';
        identify(false).then(function (d) {
          idBtn.disabled = false;
          var have = {}; photo.species.forEach(function (s) { have[s.uuid] = 1; });
          if (!d.candidates.length) {
            out.innerHTML = '<span class="text-sm text-secondary">No species recognised in this photo'
              + (d.not_in_catalog ? ' from the guide. It might be: <strong>' + esc(d.not_in_catalog) + '</strong> - <a href="' + cfg.newUrl + '">add it as your own species</a>' : '') + '.</span>';
            return;
          }
          out.innerHTML = d.candidates.map(function (c) {
            var pic = c.photo_url ? '<img src="' + c.photo_url + '" alt="">' : '<span class="dh-cat-icon" style="--icon:url(\'' + c.icon_url + '\')"></span>';
            return '<button type="button" class="dh-whats-this-card' + (have[c.uuid] ? ' is-tagged' : '') + '" data-uuid="' + c.uuid + '">' + pic
              + '<span><strong>' + esc(c.name) + '</strong><em>' + esc(c.scientific || '') + '</em></span>'
              + '<span class="dh-whats-this-conf is-' + c.confidence + '">' + (have[c.uuid] ? 'Tagged' : CONF[c.confidence]) + '</span></button>';
          }).join('') + (d.not_in_catalog ? '<span class="text-xs text-secondary">Also possibly: ' + esc(d.not_in_catalog) + ' (not in the guide)</span>' : '')
            + '<span class="text-xs text-secondary">Suggestions by AI - tap the right one to tag it.</span>';
          out.querySelectorAll('[data-uuid]').forEach(function (b) {
            if (b.classList.contains('is-tagged')) return;
            b.addEventListener('click', function () {
              askShareOnce().then(function () {
                return send('POST', cfg.photoUrl + photo.uuid + '/species', { species: b.getAttribute('data-uuid') });
              }).then(function (r) {
                apply(r);
                b.classList.add('is-tagged');
                b.querySelector('.dh-whats-this-conf').textContent = 'Tagged';
              }).catch(function (e) { alert(e.message); });
            });
          });
        }).catch(function (e) { idBtn.disabled = false; out.innerHTML = '<span class="text-sm text-danger">' + esc(e.message) + '</span>'; });
      });
    }
    function draw() {
      box.innerHTML = '';
      photo.species.forEach(function (s) {
        var c = document.createElement('span');
        c.className = 'dh-species-chip';
        c.innerHTML = esc(s.name) + '<button type="button" class="dh-species-chip-x" aria-label="Remove ' + esc(s.name) + '"><span class="material-icons-round" aria-hidden="true">close</span></button>';
        c.querySelector('button').addEventListener('click', function () {
          send('DELETE', cfg.photoUrl + photo.uuid + '/species/' + s.uuid).then(apply).catch(function (e) { alert(e.message); });
        });
        box.appendChild(c);
      });
    }
    function apply(d) {
      photo = d.photo; onChange(photo);
      tagged = d.species; renderDive();
      draw();
    }
    picker(host.querySelector('.dh-species-picker'), 'Tag a species in this photo', function () {
      var m = {}; photo.species.forEach(function (s) { m[s.uuid] = 1; }); return m;
    }, function (uuid) {
      askShareOnce().then(function () {
        return send('POST', cfg.photoUrl + photo.uuid + '/species', { species: uuid });
      }).then(apply).catch(function (e) { alert(e.message); });
    });
    draw();
  };
})();
