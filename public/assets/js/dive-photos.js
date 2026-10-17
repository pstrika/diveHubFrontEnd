/*
 * Photos card on the dive page (Pablo, 2026-10-17). Uploads one file per
 * request (phone pictures are 5-15 MB each), one at a time, and redraws
 * the grid from the JSON the server returns. The viewer edits the caption,
 * shares the photo to the site page (normal photo review) and deletes.
 * window.dhPhotoSpecies (dive-species.js), when present, draws the species
 * chips for the open photo.
 */
(function () {
  var cfg = window.dhDivePhotos;
  var grid = document.getElementById('divePhotosGrid');
  if (!cfg || !grid) return;

  var photos = cfg.photos.slice();
  var input = document.getElementById('divePhotosInput');
  var addBtn = document.getElementById('divePhotosAdd');
  var status = document.getElementById('divePhotosStatus');
  var empty = document.getElementById('divePhotosEmpty');
  var count = document.getElementById('divePhotosCount');

  var viewer = document.getElementById('dhPhotoViewer');
  // Out of the page layout, so nothing on the page can stack above it.
  document.body.appendChild(viewer);
  var vImg = document.getElementById('dhPhotoViewerImg');
  var vMeta = document.getElementById('dhPhotoViewerMeta');
  var vCaption = document.getElementById('dhPhotoViewerCaption');
  var vShare = document.getElementById('dhPhotoViewerShare');
  var vShareStatus = document.getElementById('dhPhotoViewerShareStatus');
  var vDelete = document.getElementById('dhPhotoViewerDelete');
  var current = -1;

  var SHARE_LABELS = { pending: 'Waiting for review', approved: 'On the site page', rejected: 'Not approved' };

  function send(method, url, body) {
    var opts = { method: method, headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' }, credentials: 'same-origin' };
    if (body instanceof FormData) opts.body = body;
    else if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(url, opts).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) throw new Error((data.errors && Object.values(data.errors)[0][0]) || data.message || 'Something went wrong');
        return data;
      });
    });
  }

  function render() {
    grid.innerHTML = '';
    photos.forEach(function (p, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'dh-dive-photo';
      b.setAttribute('aria-label', p.caption || 'Photo ' + (i + 1));
      var img = document.createElement('img');
      img.src = p.thumb; img.alt = ''; img.loading = 'lazy';
      b.appendChild(img);
      if (p.shared) {
        var badge = document.createElement('span');
        badge.className = 'dh-dive-photo-badge is-' + p.shared;
        badge.title = SHARE_LABELS[p.shared] || '';
        badge.innerHTML = '<span class="material-icons-round" aria-hidden="true">' + (p.shared === 'approved' ? 'public' : p.shared === 'rejected' ? 'block' : 'schedule') + '</span>';
        b.appendChild(badge);
      }
      b.addEventListener('click', function () { open(i); });
      grid.appendChild(b);
    });
    empty.hidden = photos.length > 0;
    count.textContent = photos.length + '/' + cfg.max;
    addBtn.hidden = photos.length >= cfg.max;
  }

  // Uploads, one after another.
  input.addEventListener('change', function () {
    var files = Array.prototype.slice.call(input.files || []);
    input.value = '';
    var room = cfg.max - photos.length;
    if (files.length > room) {
      status.textContent = 'Only ' + room + ' more photo' + (room === 1 ? '' : 's') + ' fit on this dive - adding the first ' + room + '.';
      files = files.slice(0, room);
    }
    var done = 0, failed = [];
    (function next() {
      if (!files.length) {
        status.textContent = failed.length ? failed.join(' ') : (done ? done + ' photo' + (done === 1 ? '' : 's') + ' added.' : '');
        return;
      }
      var f = files.shift();
      status.textContent = 'Uploading ' + (done + failed.length + 1) + ' of ' + (done + failed.length + 1 + files.length) + '...';
      var fd = new FormData();
      fd.append('photo', f);
      send('POST', cfg.uploadUrl, fd).then(function (data) {
        photos.push(data.photo); done++; render(); next();
      }).catch(function (e) {
        failed.push(f.name + ': ' + e.message); next();
      });
    })();
  });

  // Viewer.
  function open(i) {
    current = i;
    var p = photos[i];
    vImg.src = p.url;
    vImg.alt = p.caption || '';
    vMeta.textContent = (p.taken_at ? 'Taken ' + p.taken_at : '') + '  ' + (i + 1) + ' of ' + photos.length;
    vCaption.value = p.caption || '';
    if (vShare) {
      vShare.checked = !!p.shared && p.shared !== 'rejected';
      vShareStatus.textContent = p.shared ? (SHARE_LABELS[p.shared] || '') : '';
    }
    if (window.dhPhotoSpecies) window.dhPhotoSpecies(p, function (updated) { photos[current] = updated; });
    viewer.hidden = false;
    document.body.classList.add('dh-photo-viewer-open');
  }
  function close() {
    viewer.hidden = true;
    document.body.classList.remove('dh-photo-viewer-open');
    current = -1;
  }
  function step(d) {
    if (!photos.length) return;
    open((current + d + photos.length) % photos.length);
  }
  viewer.querySelector('[data-pv-close]').addEventListener('click', close);
  viewer.querySelector('[data-pv-prev]').addEventListener('click', function () { step(-1); });
  viewer.querySelector('[data-pv-next]').addEventListener('click', function () { step(1); });
  viewer.addEventListener('click', function (e) { if (e.target === viewer) close(); });
  document.addEventListener('keydown', function (e) {
    if (viewer.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft' && document.activeElement !== vCaption) step(-1);
    if (e.key === 'ArrowRight' && document.activeElement !== vCaption) step(1);
  });

  // Caption saves when you leave the field (or press Enter).
  function saveCaption() {
    var p = photos[current];
    if (!p || (p.caption || '') === vCaption.value.trim()) return;
    var i = current;
    send('PATCH', p.update_url, { caption: vCaption.value.trim() }).then(function (data) {
      photos[i] = data.photo; render();
    }).catch(function (e) { alert(e.message); });
  }
  vCaption.addEventListener('change', saveCaption);
  vCaption.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); vCaption.blur(); } });

  if (vShare) {
    vShare.addEventListener('change', function () {
      var p = photos[current], i = current;
      if (!vShare.checked && p.shared === 'approved' && !confirm('Take this photo off the site page?')) { vShare.checked = true; return; }
      vShare.disabled = true;
      send('POST', p.share_url, { share: vShare.checked }).then(function (data) {
        photos[i] = data.photo; render();
        vShareStatus.textContent = data.photo.shared ? (SHARE_LABELS[data.photo.shared] || '') : '';
      }).catch(function (e) {
        vShare.checked = !vShare.checked; alert(e.message);
      }).then(function () { vShare.disabled = false; });
    });
  }

  vDelete.addEventListener('click', function () {
    var p = photos[current];
    if (!p || !confirm(p.shared ? 'Delete this photo? It will also come off the site page.' : 'Delete this photo?')) return;
    var i = current;
    send('DELETE', cfg.deleteUrl + encodeURIComponent(p.uuid)).then(function () {
      photos.splice(i, 1); render();
      if (photos.length) open(Math.min(i, photos.length - 1)); else close();
    }).catch(function (e) { alert(e.message); });
  });

  render();
})();
