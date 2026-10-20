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

  // Big camera JPEGs (Pablo's run 20-30 MB) hit the 20 MB upload limit, and
  // the server throws the original away anyway (1600 px WebP). So the browser
  // shrinks anything over 3 MB or 2000 px to a 2000 px JPEG first
  // (2026-10-20), and sends the camera's date read from the original's EXIF.
  var SHRINK_MAX_PX = 2000, SHRINK_OVER_BYTES = 3 * 1024 * 1024;
  function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return Promise.resolve(file);
    return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bmp) {
      var w = bmp.width, h = bmp.height, scale = Math.min(1, SHRINK_MAX_PX / Math.max(w, h));
      if (scale === 1 && file.size <= SHRINK_OVER_BYTES) { bmp.close(); return file; }
      var c = document.createElement('canvas');
      c.width = Math.round(w * scale); c.height = Math.round(h * scale);
      c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
      bmp.close();
      return new Promise(function (resolve) {
        c.toBlob(function (b) {
          resolve(b ? new File([b], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
        }, 'image/jpeg', 0.85);
      });
    }).catch(function () { return file; });
  }
  // EXIF DateTimeOriginal (0x9003), else DateTime (0x0132), as "YYYY-MM-DD HH:MM:SS".
  function exifDate(file) {
    if (file.type !== 'image/jpeg') return Promise.resolve(null);
    return file.slice(0, 256 * 1024).arrayBuffer().then(function (buf) {
      var v = new DataView(buf);
      if (v.byteLength < 4 || v.getUint16(0) !== 0xFFD8) return null;
      var off = 2;
      while (off + 4 <= v.byteLength) {
        var marker = v.getUint16(off);
        if ((marker & 0xFF00) !== 0xFF00) return null;
        var len = v.getUint16(off + 2);
        if (marker === 0xFFE1 && v.getUint32(off + 4) === 0x45786966) { // "Exif"
          var tiff = off + 10;
          var little = v.getUint16(tiff) === 0x4949;
          var g16 = function (o) { return v.getUint16(o, little); }, g32 = function (o) { return v.getUint32(o, little); };
          var scan = function (ifd, want) { var n = g16(ifd); for (var i = 0; i < n; i++) { var e = ifd + 2 + i * 12; if (e + 12 > v.byteLength) return -1; if (g16(e) === want) return e; } return -1; };
          var ascii = function (e) { var count = g32(e + 4), at = count > 4 ? tiff + g32(e + 8) : e + 8, s = ''; for (var i = 0; i < count - 1 && at + i < v.byteLength; i++) s += String.fromCharCode(v.getUint8(at + i)); return s; };
          var ifd0 = tiff + g32(tiff + 4), date = null;
          var exifPtr = scan(ifd0, 0x8769);
          if (exifPtr >= 0) { var d = scan(tiff + g32(exifPtr + 8), 0x9003); if (d >= 0) date = ascii(d); }
          if (!date) { var d2 = scan(ifd0, 0x0132); if (d2 >= 0) date = ascii(d2); }
          return date && /^\d{4}:\d{2}:\d{2} \d{2}:\d{2}:\d{2}$/.test(date) ? date.replace(/^(\d{4}):(\d{2}):(\d{2})/, '$1-$2-$3') : null;
        }
        if (marker === 0xFFDA) return null; // image data starts: no EXIF
        off += 2 + len;
      }
      return null;
    }).catch(function () { return null; });
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
      // A species tagged on this photo: reef-fish badge, bottom right, as in the app.
      if (p.species && p.species.length) {
        var fish = document.createElement('span');
        fish.className = 'dh-dive-photo-badge is-species';
        fish.title = p.species.map(function (s) { return s.name; }).join(', ');
        fish.innerHTML = '<span class="dh-cat-icon" style="--icon:url(' + cfg.fishIcon + ')" aria-hidden="true"></span>';
        b.appendChild(fish);
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
      status.textContent = 'Preparing ' + (done + failed.length + 1) + ' of ' + (done + failed.length + 1 + files.length) + '...';
      Promise.all([shrink(f), exifDate(f)]).then(function (r) {
        status.textContent = 'Uploading ' + (done + failed.length + 1) + ' of ' + (done + failed.length + 1 + files.length) + '...';
        var fd = new FormData();
        fd.append('photo', r[0]);
        if (r[1]) fd.append('taken_at', r[1]);
        return send('POST', cfg.uploadUrl, fd);
      }).then(function (data) {
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
    if (window.dhPhotoSpecies) window.dhPhotoSpecies(p, function (updated) { photos[current] = updated; render(); });
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
