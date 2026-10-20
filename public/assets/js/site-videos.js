/*
 * "Add a video" on a site page (Pablo, 2026-10-20): the file goes straight
 * from the browser to Cloudflare Stream with tus (resumable), then the
 * server is told it's done and the video waits for admin review.
 * window.dhSiteVideos: {createUrl, csrf, maxBytes, siteName}.
 */
(function () {
  var cfg = window.dhSiteVideos;
  var modalEl = document.getElementById('dh-upload-site-video-modal');
  if (!cfg || !modalEl) return;

  var input = document.getElementById('dh-site-video-input');
  var picked = document.getElementById('dh-site-video-picked');
  var submitBtn = document.getElementById('dh-site-video-submit');
  var cancelBtn = document.getElementById('dh-site-video-cancel');
  var formState = document.getElementById('dh-site-video-form-state');
  var progress = document.getElementById('dh-site-video-progress');
  var bar = document.getElementById('dh-site-video-progress-bar');
  var progressText = document.getElementById('dh-site-video-progress-text');
  var successEl = document.getElementById('dh-site-video-success');
  var successText = document.getElementById('dh-site-video-success-text');
  var errorEl = document.getElementById('dh-site-video-error');
  var file = null, didUpload = false, busy = false;

  function fmtBytes(n) { return n >= 1e9 ? (n / 1e9).toFixed(2) + ' GB' : Math.round(n / 1e6) + ' MB'; }
  function fail(msg) { errorEl.textContent = msg; errorEl.hidden = false; }

  function send(method, url, body) {
    return fetch(url, {
      method: method, credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) throw new Error((data.errors && Object.values(data.errors)[0][0]) || data.message || 'Something went wrong');
        return data;
      });
    });
  }

  input.addEventListener('change', function () {
    file = input.files && input.files[0] || null;
    errorEl.hidden = true;
    if (!file) { picked.textContent = ''; submitBtn.disabled = true; return; }
    if (file.size > cfg.maxBytes) { fail('That video is ' + fmtBytes(file.size) + ' - the limit is 1 GB.'); file = null; submitBtn.disabled = true; return; }
    picked.textContent = file.name + ' (' + fmtBytes(file.size) + ')';
    submitBtn.disabled = false;
  });

  submitBtn.addEventListener('click', function () {
    if (!file || busy) return;
    if (!window.tus) { fail('The uploader did not load. Please reload the page and try again.'); return; }
    busy = true; submitBtn.disabled = true; submitBtn.textContent = 'Uploading…';
    cancelBtn.disabled = true;
    progress.hidden = false; bar.style.width = '0%';
    var f = file;
    send('POST', cfg.createUrl, { name: f.name, size: f.size }).then(function (d) {
      var upload = new tus.Upload(f, {
        uploadUrl: d.upload_url,
        chunkSize: 50 * 1024 * 1024,
        retryDelays: [0, 1000, 3000, 5000, 10000, 20000],
        metadata: { name: f.name, filetype: f.type },
        onProgress: function (sent, total) {
          var pct = Math.round(sent / total * 100);
          bar.style.width = pct + '%';
          progressText.textContent = 'Uploading - ' + pct + '% of ' + fmtBytes(total);
        },
        onError: function (e) {
          busy = false; cancelBtn.disabled = false; submitBtn.disabled = false; submitBtn.textContent = 'Submit for review';
          progress.hidden = true;
          fail('Upload failed: ' + (e && e.message ? e.message : e) + '. Try again.');
        },
        onSuccess: function () {
          bar.style.width = '100%';
          send('POST', d.uploaded_url).catch(function () {}).then(function () {
            didUpload = true; busy = false;
            formState.hidden = true; successEl.hidden = false; submitBtn.hidden = true;
            cancelBtn.disabled = false; cancelBtn.textContent = 'Close';
            successText.textContent = 'Thanks! Your video is pending review and will show on ' + cfg.siteName + "'s page once approved. Processing takes a minute or two.";
          });
        }
      });
      upload.start();
    }).catch(function (e) {
      busy = false; cancelBtn.disabled = false; submitBtn.disabled = false; submitBtn.textContent = 'Submit for review';
      progress.hidden = true;
      fail(e.message);
    });
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    if (didUpload) window.location.reload();
  });
})();
