/*
 * Group page (Pablo, 2026-10-20): the chat's "Add a video" (straight to
 * Cloudflare Stream with tus, attached when the message is posted) and the
 * "Photos & videos" gallery - every chat upload, with a photo/video badge
 * and the uploader's avatar; the uploader or a group admin can delete from
 * the viewer. window.dhGroupMedia: {media, csrf, listUrl, videoCreateUrl,
 * maxBytes, uploadsOn}.
 */
(function () {
  var cfg = window.dhGroupMedia;
  if (!cfg) return;

  function send(method, url, body) {
    var opts = { method: method, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' } };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(url, opts).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) throw new Error((data.errors && Object.values(data.errors)[0][0]) || data.message || 'Something went wrong');
        return data;
      });
    });
  }
  function fmtBytes(n) { return n >= 1e9 ? (n / 1e9).toFixed(2) + ' GB' : Math.round(n / 1e6) + ' MB'; }
  function esc(s) { return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  // ---- Chat: attach a video ------------------------------------------------
  var videoIds = [];           // attached to the next message
  var videoThumbs = {};        // id -> thumb url (when ready)
  window.dhChatVideoIds = videoIds;
  var preview = document.getElementById('chatVideoPreview');
  function drawPreview() {
    if (!preview) return;
    preview.innerHTML = videoIds.map(function (id) {
      return videoThumbs[id]
        ? '<img src="' + esc(videoThumbs[id]) + '" style="width:32px;height:32px;object-fit:cover;" class="border-radius-sm me-1">'
        : '<span class="material-icons-round text-secondary me-1" style="font-size:22px;">videocam</span>';
    }).join('') + (videoIds.length ? '<span class="text-xs text-secondary">' + videoIds.length + ' video' + (videoIds.length === 1 ? '' : 's') + ' attached</span>' : '');
  }
  window.dhChatVideoReset = function () { videoIds.length = 0; videoThumbs = {}; drawPreview(); };

  var modalEl = document.getElementById('modalChatVideo');
  if (modalEl && cfg.uploadsOn) {
    var input = document.getElementById('chatVideoInput');
    var picked = document.getElementById('chatVideoPicked');
    var attachBtn = document.getElementById('chatVideoAttach');
    var cancelBtn = document.getElementById('chatVideoCancel');
    var progress = document.getElementById('chatVideoProgress');
    var bar = document.getElementById('chatVideoProgressBar');
    var progressText = document.getElementById('chatVideoProgressText');
    var errorEl = document.getElementById('chatVideoError');
    var file = null, busy = false;
    function fail(msg) { errorEl.textContent = msg; errorEl.hidden = false; }
    function reset() {
      file = null; busy = false; input.value = ''; picked.textContent = ''; errorEl.hidden = true;
      attachBtn.disabled = true; attachBtn.textContent = 'Attach'; cancelBtn.disabled = false; progress.hidden = true; bar.style.width = '0%';
      var name = modalEl.querySelector('.dh-file-picker-name'); if (name) name.textContent = 'Choose a video…';
    }
    input.addEventListener('change', function () {
      file = input.files && input.files[0] || null;
      errorEl.hidden = true;
      if (!file) { picked.textContent = ''; attachBtn.disabled = true; return; }
      if (file.size > cfg.maxBytes) { fail('That video is ' + fmtBytes(file.size) + ' - the limit is 1 GB.'); file = null; attachBtn.disabled = true; return; }
      picked.textContent = file.name + ' (' + fmtBytes(file.size) + ')';
      attachBtn.disabled = false;
    });
    attachBtn.addEventListener('click', function () {
      if (!file || busy) return;
      if (!window.tus) { fail('The uploader did not load. Please reload the page and try again.'); return; }
      busy = true; attachBtn.disabled = true; attachBtn.textContent = 'Uploading…'; cancelBtn.disabled = true;
      progress.hidden = false; bar.style.width = '0%';
      var f = file;
      send('POST', cfg.videoCreateUrl, { name: f.name, size: f.size }).then(function (d) {
        var upload = new tus.Upload(f, {
          uploadUrl: d.upload_url, chunkSize: 50 * 1024 * 1024, retryDelays: [0, 1000, 3000, 5000, 10000, 20000],
          metadata: { name: f.name, filetype: f.type },
          onProgress: function (sent, total) { var pct = Math.round(sent / total * 100); bar.style.width = pct + '%'; progressText.textContent = 'Uploading - ' + pct + '% of ' + fmtBytes(total); },
          onError: function (e) { busy = false; cancelBtn.disabled = false; attachBtn.disabled = false; attachBtn.textContent = 'Attach'; progress.hidden = true; fail('Upload failed: ' + (e && e.message ? e.message : e) + '. Try again.'); },
          onSuccess: function () {
            bar.style.width = '100%';
            send('POST', d.uploaded_url).catch(function () { return {}; }).then(function (r) {
              videoIds.push(d.id); if (r && r.thumb) videoThumbs[d.id] = r.thumb; drawPreview();
              // The thumbnail appears once Cloudflare has processed it.
              (function poll(n) {
                if (videoThumbs[d.id] || n > 40 || videoIds.indexOf(d.id) === -1) return;
                setTimeout(function () {
                  send('GET', d.status_url).then(function (s) { if (s.thumb) { videoThumbs[d.id] = s.thumb; drawPreview(); } else poll(n + 1); }).catch(function () { poll(n + 1); });
                }, 5000);
              })(0);
              reset();
              bootstrap.Modal.getInstance(modalEl).hide();
            });
          }
        });
        upload.start();
      }).catch(function (e) { busy = false; cancelBtn.disabled = false; attachBtn.disabled = false; attachBtn.textContent = 'Attach'; progress.hidden = true; fail(e.message); });
    });
    modalEl.addEventListener('hidden.bs.modal', function () { if (!busy) reset(); });
  }

  // ---- Gallery -------------------------------------------------------------
  var grid = document.getElementById('groupMediaGrid');
  var empty = document.getElementById('groupMediaEmpty');
  var count = document.getElementById('groupMediaCount');
  var media = (cfg.media || []).slice();
  var current = null;
  var viewer = document.getElementById('modalGroupMedia');
  var vImg = document.getElementById('groupMediaImg');
  var vFrame = document.getElementById('groupMediaFrame');
  var vAvatar = document.getElementById('groupMediaAvatar');
  var vName = document.getElementById('groupMediaName');
  var vWhen = document.getElementById('groupMediaWhen');
  var vCaption = document.getElementById('groupMediaCaption');
  var vDelete = document.getElementById('groupMediaDelete');

  function render() {
    if (!grid) return;
    grid.innerHTML = '';
    media.forEach(function (m, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'dh-group-media-tile is-' + m.kind + (m.status !== 'ready' ? ' is-' + m.status : '');
      b.setAttribute('aria-label', (m.kind === 'video' ? 'Video' : 'Photo') + ' by ' + m.user.name);
      var html = '';
      if (m.status === 'ready' && m.thumb) html += '<img src="' + esc(m.thumb) + '" alt="" loading="lazy">';
      else html += '<span class="dh-dive-video-status">' + (m.status === 'failed' ? '<span class="material-icons-round" aria-hidden="true">error_outline</span><span>Failed</span>' : '<span class="dh-spinner" aria-hidden="true"></span><span>Processing…</span>') + '</span>';
      html += '<span class="dh-group-media-kind" title="' + (m.kind === 'video' ? 'Video' : 'Photo') + '"><span class="material-icons-round" aria-hidden="true">' + (m.kind === 'video' ? 'videocam' : 'photo_camera') + '</span></span>';
      if (m.kind === 'video' && m.duration && m.status === 'ready') html += '<span class="dh-dive-video-duration">' + esc(m.duration) + '</span>';
      html += '<img class="dh-group-media-avatar" src="' + esc(m.user.avatar) + '" alt="" title="' + esc(m.user.name) + '">';
      b.innerHTML = html;
      b.addEventListener('click', function () { open(i); });
      grid.appendChild(b);
    });
    if (empty) empty.hidden = media.length > 0;
    if (count) count.textContent = media.length;
    if (media.some(function (m) { return m.status === 'processing' || m.status === 'uploading'; })) setTimeout(refresh, 6000);
  }

  function refresh() {
    send('GET', cfg.listUrl).then(function (d) { media = d.media; render(); }).catch(function () {});
  }
  window.dhGroupMediaRefresh = refresh;

  function open(i) {
    var m = media[i];
    if (!m || !viewer) return;
    if (m.status !== 'ready') {
      if (m.status === 'failed' && m.can_delete && confirm('This video could not be processed. Remove it?')) doDelete(m);
      return;
    }
    current = m;
    vImg.hidden = m.kind !== 'photo'; vFrame.hidden = m.kind !== 'video';
    vImg.src = m.kind === 'photo' ? m.url : ''; vFrame.src = m.kind === 'video' ? m.url : 'about:blank';
    vAvatar.src = m.user.avatar; vName.textContent = m.user.name; vWhen.textContent = m.when_label;
    vCaption.textContent = m.caption || ''; vCaption.hidden = !m.caption;
    vDelete.hidden = !m.can_delete;
    new bootstrap.Modal(viewer).show();
  }
  function doDelete(m) {
    send('DELETE', m.delete_url).then(function (d) {
      media = d.media; render();
      var inst = viewer && bootstrap.Modal.getInstance(viewer); if (inst) inst.hide();
      if (window.refreshChatMessages) window.refreshChatMessages();
    }).catch(function (e) { alert(e.message); });
  }
  if (viewer) {
    viewer.addEventListener('hidden.bs.modal', function () { vFrame.src = 'about:blank'; vImg.src = ''; current = null; });
    vDelete.addEventListener('click', function () {
      if (!current || !confirm('Delete this ' + (current.kind === 'video' ? 'video' : 'photo') + ' from the group?')) return;
      doDelete(current);
    });
  }
  render();
})();
