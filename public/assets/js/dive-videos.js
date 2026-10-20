/*
 * Videos card on the dive page (Pablo, 2026-10-20). The file goes straight
 * from the browser to Cloudflare Stream with tus (resumable, so a dropped
 * connection on a 500 MB GoPro clip picks up where it stopped): we ask our
 * server for an upload (it creates the row), upload, tell the server it's
 * done, then poll until Cloudflare has processed the video. Playback is
 * Cloudflare's player in an iframe with a short-lived owner token.
 */
(function () {
  var cfg = window.dhDiveVideos;
  var grid = document.getElementById('diveVideosGrid');
  if (!cfg || !grid) return;

  var videos = cfg.videos.slice();
  var input = document.getElementById('diveVideosInput');
  var addBtn = document.getElementById('diveVideosAdd');
  var status = document.getElementById('diveVideosStatus');
  var progress = document.getElementById('diveVideosProgress');
  var progressBar = document.getElementById('diveVideosProgressBar');
  var empty = document.getElementById('diveVideosEmpty');
  var count = document.getElementById('diveVideosCount');

  var viewer = document.getElementById('dhVideoViewer');
  document.body.appendChild(viewer);
  var vFrame = document.getElementById('dhVideoViewerFrame');
  var vMeta = document.getElementById('dhVideoViewerMeta');
  var vCaption = document.getElementById('dhVideoViewerCaption');
  var vShare = document.getElementById('dhVideoViewerShare');
  var vShareStatus = document.getElementById('dhVideoViewerShareStatus');
  var vDelete = document.getElementById('dhVideoViewerDelete');
  var current = -1;
  var uploading = false;

  var SHARE_LABELS = { pending: 'Waiting for review', approved: 'On the site page', rejected: 'Not approved' };
  var STATUS_LABELS = { uploading: 'Uploading…', processing: 'Processing…', failed: 'Failed' };

  function send(method, url, body) {
    var opts = { method: method, headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' }, credentials: 'same-origin' };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(url, opts).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) throw new Error((data.errors && Object.values(data.errors)[0][0]) || data.message || 'Something went wrong');
        return data;
      });
    });
  }

  function render() {
    grid.innerHTML = '';
    videos.forEach(function (v, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'dh-dive-video' + (v.status === 'ready' ? '' : ' is-' + v.status);
      b.setAttribute('aria-label', v.caption || v.name || 'Video ' + (i + 1));
      if (v.status === 'ready' && v.thumb) {
        var img = document.createElement('img');
        img.src = v.thumb; img.alt = ''; img.loading = 'lazy';
        b.appendChild(img);
        var play = document.createElement('span');
        play.className = 'dh-dive-video-play';
        play.innerHTML = '<span class="material-icons-round" aria-hidden="true">play_arrow</span>';
        b.appendChild(play);
        if (v.duration) {
          var d = document.createElement('span');
          d.className = 'dh-dive-video-duration';
          d.textContent = v.duration;
          b.appendChild(d);
        }
      } else {
        var s = document.createElement('span');
        s.className = 'dh-dive-video-status';
        s.innerHTML = (v.status === 'failed' ? '<span class="material-icons-round" aria-hidden="true">error_outline</span>' : '<span class="dh-spinner" aria-hidden="true"></span>')
          + '<span>' + (v.status === 'uploading' && typeof v.pct === 'number' ? 'Uploading ' + v.pct + '%' : (STATUS_LABELS[v.status] || v.status)) + '</span>';
        if (v.status === 'failed' && v.error) s.title = v.error;
        b.appendChild(s);
      }
      if (v.shared) {
        var badge = document.createElement('span');
        badge.className = 'dh-dive-photo-badge is-' + v.shared;
        badge.title = SHARE_LABELS[v.shared] || '';
        badge.innerHTML = '<span class="material-icons-round" aria-hidden="true">' + (v.shared === 'approved' ? 'public' : v.shared === 'rejected' ? 'block' : 'schedule') + '</span>';
        b.appendChild(badge);
      }
      b.addEventListener('click', function () { open(i); });
      grid.appendChild(b);
    });
    empty.hidden = videos.length > 0;
    count.textContent = videos.length + '/' + cfg.max;
    addBtn.hidden = videos.length >= cfg.max || uploading;
    schedulePoll();
  }

  // Ask the server about videos still being processed, every 5 s.
  var pollTimer = null;
  function schedulePoll() {
    if (pollTimer) return;
    var pending = videos.filter(function (v) { return v.status === 'processing'; });
    if (!pending.length) return;
    pollTimer = setTimeout(function () {
      pollTimer = null;
      Promise.all(pending.map(function (v) {
        return send('GET', v.status_url).then(function (d) {
          var i = videos.findIndex(function (x) { return x.uuid === v.uuid; });
          if (i >= 0) videos[i] = d.video;
        }).catch(function () {});
      })).then(function () {
        if (!videos.some(function (v) { return v.status === 'processing'; }) && /processing/.test(status.textContent)) status.textContent = 'Your video is ready.';
        render();
      });
    }, 5000);
  }

  function fmtBytes(n) { return n >= 1e9 ? (n / 1e9).toFixed(2) + ' GB' : Math.round(n / 1e6) + ' MB'; }

  input.addEventListener('change', function () {
    var f = input.files && input.files[0];
    input.value = '';
    if (!f) return;
    if (f.size > cfg.maxBytes) { status.textContent = 'That video is ' + fmtBytes(f.size) + ' - the limit is 1 GB.'; return; }
    if (!window.tus) { status.textContent = 'The uploader did not load. Please reload the page and try again.'; return; }
    uploading = true;
    addBtn.hidden = true;
    status.textContent = 'Preparing upload…';
    progress.hidden = false; progressBar.style.width = '0%';
    var entry = null;
    send('POST', cfg.createUrl, { name: f.name, size: f.size }).then(function (d) {
      entry = d.video; entry.pct = 0;
      videos.push(entry); render();
      var upload = new tus.Upload(f, {
        uploadUrl: d.upload_url,
        chunkSize: 50 * 1024 * 1024,
        retryDelays: [0, 1000, 3000, 5000, 10000, 20000],
        metadata: { name: f.name, filetype: f.type },
        onProgress: function (sent, total) {
          var pct = Math.round(sent / total * 100);
          entry.pct = pct;
          progressBar.style.width = pct + '%';
          status.textContent = 'Uploading ' + f.name + ' - ' + pct + '% of ' + fmtBytes(total);
          var tile = grid.children[videos.indexOf(entry)];
          var txt = tile && tile.querySelector('.dh-dive-video-status span:last-child');
          if (txt) txt.textContent = 'Uploading ' + pct + '%';
        },
        onError: function (e) {
          uploading = false; progress.hidden = true;
          status.textContent = 'Upload failed: ' + (e && e.message ? e.message : e) + '. Try again.';
          entry.status = 'failed'; entry.error = 'Upload failed'; render();
        },
        onSuccess: function () {
          progressBar.style.width = '100%';
          status.textContent = 'Uploaded. Cloudflare is processing the video - this takes a minute or two.';
          send('POST', entry.uploaded_url).then(function (d) {
            var i = videos.indexOf(entry);
            if (i >= 0) videos[i] = d.video;
          }).catch(function (e) { status.textContent = e.message; }).then(function () {
            uploading = false; progress.hidden = true; render();
          });
        }
      });
      upload.start();
    }).catch(function (e) {
      uploading = false; progress.hidden = true;
      status.textContent = e.message;
      render();
    });
  });

  // Viewer.
  function open(i) {
    var v = videos[i];
    if (!v) return;
    if (v.status !== 'ready') {
      if (v.status === 'failed' && confirm('This video could not be processed' + (v.error ? ' (' + v.error + ')' : '') + '. Remove it?')) {
        send('DELETE', v.delete_url).then(function () { videos.splice(i, 1); render(); }).catch(function (e) { alert(e.message); });
      }
      return;
    }
    current = i;
    vFrame.src = v.player;
    vMeta.textContent = [v.name, v.duration].filter(Boolean).join(' · ');
    vCaption.value = v.caption || '';
    if (vShare) {
      vShare.checked = !!v.shared && v.shared !== 'rejected';
      vShareStatus.textContent = v.shared ? (SHARE_LABELS[v.shared] || '') : '';
    }
    viewer.hidden = false;
    document.body.classList.add('dh-photo-viewer-open');
  }
  function close() {
    viewer.hidden = true;
    vFrame.src = 'about:blank';
    document.body.classList.remove('dh-photo-viewer-open');
    current = -1;
  }
  viewer.querySelector('[data-vv-close]').addEventListener('click', close);
  viewer.addEventListener('click', function (e) { if (e.target === viewer) close(); });
  document.addEventListener('keydown', function (e) { if (!viewer.hidden && e.key === 'Escape') close(); });

  var captionTimer = null;
  vCaption.addEventListener('input', function () {
    clearTimeout(captionTimer);
    var v = videos[current], i = current;
    if (!v) return;
    captionTimer = setTimeout(function () {
      send('PATCH', v.update_url, { caption: vCaption.value }).then(function (d) { videos[i] = d.video; render(); }).catch(function (e) { alert(e.message); });
    }, 600);
  });

  if (vShare) {
    vShare.addEventListener('change', function () {
      var v = videos[current], i = current;
      if (!v) return;
      if (!vShare.checked && v.shared === 'approved' && !confirm('Take this video off the site page?')) { vShare.checked = true; return; }
      vShare.disabled = true;
      send('POST', v.share_url, { share: vShare.checked }).then(function (d) {
        videos[i] = d.video; render();
        vShareStatus.textContent = d.video.shared ? (SHARE_LABELS[d.video.shared] || '') : '';
      }).catch(function (e) { vShare.checked = !vShare.checked; alert(e.message); }).then(function () { vShare.disabled = false; });
    });
  }

  vDelete.addEventListener('click', function () {
    var v = videos[current];
    if (!v || !confirm(v.shared ? 'Delete this video? It will also come off the site page.' : 'Delete this video?')) return;
    var i = current;
    send('DELETE', v.delete_url).then(function () {
      videos.splice(i, 1); render(); close();
    }).catch(function (e) { alert(e.message); });
  });

  render();
})();
