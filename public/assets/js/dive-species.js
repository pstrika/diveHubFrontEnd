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
    host.innerHTML = '<div class="dh-species-chips"></div><div class="dh-species-picker"></div>';
    var box = host.querySelector('.dh-species-chips');
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
