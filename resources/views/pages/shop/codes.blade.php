{{--
    Shop portal: the codes behind a shop's QR signs. Each code is a short
    link, /go/CODE, that counts the scan and lands on the shop's page.

    The QR picture is drawn in the browser by one small script,
    public/assets/js/plugins/qrcode-generator.min.js (qrcode-generator,
    MIT). Until that file is in the project the picture, download and
    print buttons stay locked and only the link shows; nothing else on
    the page depends on it. The link is what the picture encodes, so
    codes made before the script arrives keep working.
--}}
@php
    $placements = \App\Http\Controllers\ShopPortalController::PLACEMENTS;
    $qrScript = 'assets/js/plugins/qrcode-generator.min.js';
    $qrReady = is_file(public_path($qrScript));
@endphp
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="codes" />

    {{-- How it works, in order, before anything else on the page. --}}
    <section class="dh-panel">
        <h2 class="dh-panel-title">How QR codes work</h2>
        <ol class="dh-shop-how">
            <li><b>You put a sign up.</b> By the register, on the boat, on a flyer.</li>
            <li><b>A diver scans it</b> and lands on your Divers Hub page.</li>
            <li><b>If they sign up, you become their home shop.</b> Your trips show first for them. A diver who already has an account is asked first.</li>
        </ol>
        @unless($hasStorefront)
            <div class="dh-shop-callout" role="alert">
                <span class="material-icons-round" aria-hidden="true">storefront</span>
                <div>
                    <b>Add your shop or dock first.</b>
                    <p>A home shop is a place: an address and hours. Until you have added your store, or your dock if you run a boat with no store, a diver who scans your code and signs up cannot be given you as their home shop, so new codes are closed until then.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ route('shop-portal.locations', ['operator' => $operator->id]) }}">Add your shop or dock</a>
                </div>
            </div>
        @endunless
    </section>

    @if($codes->isNotEmpty() || $hasStorefront)
    <section class="dh-panel">
        <h2 class="dh-panel-title">Your codes</h2>
        <p class="dh-shop-muted">One code per place you put it, so you can see which sign gets scanned.</p>
        @if($codes->isNotEmpty() && !$hasStorefront)
            <p class="dh-shop-warn">These codes still open your page and count scans, but nobody who signs up through them becomes your home shop diver until you add your shop or dock.</p>
        @endif

        @forelse($codes as $code)
            <div class="dh-shop-card {{ $code->is_active ? '' : 'is-off' }}">
                <div class="dh-shop-card-head">
                    <div>
                        <h3>{{ $placements[$code->placement] ?? 'Other' }}@if($code->label) · {{ $code->label }}@endif</h3>
                        <p><span class="dh-shop-code">{{ $code->code }}</span> · {{ (int) ($scans[$code->id] ?? 0) }} {{ Str::plural('scan', (int) ($scans[$code->id] ?? 0)) }}</p>
                    </div>
                    <div class="dh-shop-chips">
                        @unless($code->is_active)<span class="chip chip-static">Off</span>@endunless
                    </div>
                </div>
                @if($qrReady)
                    {{-- The whole sign is one picture: the shop's logo, the ask,
                         the code and the short link. The same picture is what
                         shows here, what "Download sign" saves and what "Print a
                         sign" puts on paper, so they can never differ. --}}
                    <div class="dh-shop-sign" id="sign-{{ $code->id }}">
                        <canvas class="dh-shop-signpic" width="1200" height="1600"
                                data-qr="{{ route('go', ['code' => $code->code]) }}"
                                data-qr-shop="{{ $shopName }}"
                                data-qr-logo="{{ $operator->logoUrl ? asset('assets') . $operator->logoUrl : '' }}"
                                data-qr-name="{{ Str::slug($shopName . ' ' . ($code->label ?: $code->code)) }}"
                                role="img" aria-label="Sign with a QR code for {{ $shopName }}"></canvas>
                    </div>
                @endif
                <div class="dh-shop-linkbox mt-2 dh-shop-noprint">
                    <input type="text" readonly value="{{ route('go', ['code' => $code->code]) }}" id="code-{{ $code->id }}" aria-label="Link for this code">
                    <button type="button" class="dh-btn dh-btn-ghost-dark dh-shop-noprint" data-copy="code-{{ $code->id }}">Copy link</button>
                </div>
                <div class="dh-shop-actions dh-shop-noprint">
                    @if($qrReady)
                        <button type="button" class="dh-btn dh-btn-primary" data-qr-print="sign-{{ $code->id }}"><span class="material-icons-round" aria-hidden="true">print</span>Print a sign</button>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" data-qr-download="sign-{{ $code->id }}"><span class="material-icons-round" aria-hidden="true">download</span>Download sign</button>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" data-qr-only="sign-{{ $code->id }}" title="The code alone, for your own flyers and cards"><span class="material-icons-round" aria-hidden="true">qr_code_2</span>Code only</button>
                    @else
                        <button type="button" class="dh-shop-tab is-locked" disabled title="Not switched on yet"><span class="material-icons-round" aria-hidden="true">lock</span>Download QR image</button>
                        <button type="button" class="dh-shop-tab is-locked" disabled title="Not switched on yet"><span class="material-icons-round" aria-hidden="true">lock</span>Print a sign</button>
                    @endif
                    <form class="dh-shop-inline" method="POST" action="{{ route('shop-portal.codes.toggle', ['operator' => $operator->id, 'code' => $code->id]) }}">
                        @csrf
                        <button type="submit" class="dh-btn dh-btn-ghost-dark">{{ $code->is_active ? 'Switch off' : 'Switch on' }}</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="dh-shop-muted">No codes yet. Make one below.</p>
        @endforelse
    </section>
    @endif

    @if($hasStorefront)
    <section class="dh-panel dh-shop-noprint">
        <h2 class="dh-panel-title">New code</h2>
        <form method="POST" action="{{ route('shop-portal.codes.create', ['operator' => $operator->id]) }}">
            @csrf
            <div class="dh-shop-form">
                <div class="dh-shop-field">
                    <label for="nc-place">Where will it go?</label>
                    <select id="nc-place" name="placement">
                        @foreach($placements as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="dh-shop-field">
                    <label for="nc-label">Label (only you see it)</label>
                    <input id="nc-label" name="label" maxlength="80" placeholder="Front counter">
                </div>
                @if($locations->count() > 1)
                    <div class="dh-shop-field">
                        <label for="nc-loc">Location</label>
                        <select id="nc-loc" name="operator_location_id">
                            <option value="">Your main storefront</option>
                            @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Create code</button></div>
        </form>
    </section>
    @endif

    @if($qrReady)
    <script src="{{ asset($qrScript) }}?m={{ @filemtime(public_path($qrScript)) }}"></script>
    <script>
        (function () {
            var INK = '#0b2a3a';   // the Divers Hub navy: near black, so contrast stays high
            var SEA = '#0e7c9e';
            var DH_LOGO = '{{ asset('assets') }}/img/logos/logo_circle.png';

            function load(src) {
                return new Promise(function (resolve) {
                    if (!src) return resolve(null);
                    var img = new Image();
                    img.onload = function () { resolve(img); };
                    img.onerror = function () { resolve(null); };
                    img.src = src;
                });
            }
            function rounded(ctx, x, y, w, h, r) {
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.arcTo(x + w, y, x + w, y + h, r);
                ctx.arcTo(x + w, y + h, x, y + h, r);
                ctx.arcTo(x, y + h, x, y, r);
                ctx.arcTo(x, y, x + w, y, r);
                ctx.closePath();
            }

            // The code itself, drawn into a square of `size` at (x, y).
            //   - Error correction H: 30% of the picture can be lost and it
            //     still scans. That is what pays for the logo in the middle
            //     and for a sign that gets scuffed on a shop counter.
            //   - Every module keeps its full square footprint (only the
            //     corners are softened), and the three corner "eyes" keep the
            //     exact 7:5:3 proportions scanners look for.
            //   - The logo covers a centred square about a fifth of the width,
            //     roughly 5% of the modules, well inside what H recovers.
            //   - Four modules of clear margin all round, as the standard asks.
            function drawCode(ctx, text, x, y, size, centre) {
                var qr = qrcode(0, 'H');
                qr.addData(text);
                qr.make();
                var n = qr.getModuleCount(), margin = 4;
                var cell = Math.floor(size / (n + margin * 2));
                var ox = x + Math.floor((size - cell * n) / 2), oy = y + Math.floor((size - cell * n) / 2);

                ctx.fillStyle = '#fff';
                ctx.fillRect(x, y, size, size);

                var hole = centre ? (Math.round(n * 0.2) | 1) : 0;      // odd, so it centres on a module
                var h0 = (n - hole) / 2, h1 = h0 + hole;
                var inEye = function (r, c) { return (r < 7 && c < 7) || (r < 7 && c >= n - 7) || (r >= n - 7 && c < 7); };

                ctx.fillStyle = INK;
                for (var r = 0; r < n; r++) {
                    for (var c = 0; c < n; c++) {
                        if (!qr.isDark(r, c) || inEye(r, c)) continue;
                        if (hole && r >= h0 && r < h1 && c >= h0 && c < h1) continue;
                        rounded(ctx, ox + c * cell, oy + r * cell, cell, cell, cell * 0.28);
                        ctx.fill();
                    }
                }
                [[0, 0], [0, n - 7], [n - 7, 0]].forEach(function (eye) {
                    var ex = ox + eye[1] * cell, ey = oy + eye[0] * cell;
                    ctx.fillStyle = INK;  rounded(ctx, ex, ey, cell * 7, cell * 7, cell * 1.6); ctx.fill();
                    ctx.fillStyle = '#fff'; rounded(ctx, ex + cell, ey + cell, cell * 5, cell * 5, cell * 1.1); ctx.fill();
                    ctx.fillStyle = SEA;  rounded(ctx, ex + cell * 2, ey + cell * 2, cell * 3, cell * 3, cell * 0.7); ctx.fill();
                });
                if (hole && centre) {
                    var hx = ox + h0 * cell, hy = oy + h0 * cell, hs = hole * cell;
                    ctx.fillStyle = '#fff';
                    rounded(ctx, hx, hy, hs, hs, cell); ctx.fill();
                    ctx.drawImage(centre, hx + cell * 0.5, hy + cell * 0.5, hs - cell, hs - cell);
                }
            }

            function drawSign(canvas, shopLogo, dhLogo) {
                var ctx = canvas.getContext('2d'), W = canvas.width, H = canvas.height;
                var url = canvas.getAttribute('data-qr'), shop = canvas.getAttribute('data-qr-shop');
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, W, H);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // A band of colour across the top and bottom frames the sign.
                ctx.fillStyle = INK; ctx.fillRect(0, 0, W, 26); ctx.fillRect(0, H - 150, W, 150);

                // The shop: its logo if we have one, else its name.
                if (shopLogo) {
                    var maxW = 640, maxH = 190, k = Math.min(maxW / shopLogo.width, maxH / shopLogo.height);
                    var lw = shopLogo.width * k, lh = shopLogo.height * k;
                    ctx.drawImage(shopLogo, (W - lw) / 2, 70 + (maxH - lh) / 2, lw, lh);
                } else {
                    ctx.fillStyle = INK;
                    ctx.font = '800 84px Roboto, "Helvetica Neue", Arial, sans-serif';
                    ctx.fillText(shop, W / 2, 165, W - 160);
                }

                ctx.fillStyle = INK;
                ctx.font = '800 78px Roboto, "Helvetica Neue", Arial, sans-serif';
                ctx.fillText('Scan for our dive trips', W / 2, 345, W - 120);
                ctx.fillStyle = '#5b6b73';
                ctx.font = '500 38px Roboto, "Helvetica Neue", Arial, sans-serif';
                ctx.fillText('Schedule, conditions and prices, on your phone', W / 2, 420, W - 120);

                // The code, on a soft panel so it reads as the thing to point at.
                var size = 880, qx = (W - size) / 2, qy = 480;
                ctx.fillStyle = '#eef5f8';
                rounded(ctx, qx - 30, qy - 30, size + 60, size + 60, 48); ctx.fill();
                ctx.fillStyle = '#fff';
                rounded(ctx, qx, qy, size, size, 30); ctx.fill();
                ctx.save(); rounded(ctx, qx, qy, size, size, 30); ctx.clip();
                drawCode(ctx, url, qx, qy, size, dhLogo);
                ctx.restore();

                // Footer: who runs it, and the link for anyone who would rather type.
                var fy = H - 75;
                if (dhLogo) ctx.drawImage(dhLogo, 70, fy - 42, 84, 84);
                ctx.textAlign = 'left';
                ctx.fillStyle = '#fff';
                ctx.font = '800 44px Roboto, "Helvetica Neue", Arial, sans-serif';
                ctx.fillText('Divers Hub', dhLogo ? 176 : 70, fy);
                ctx.textAlign = 'right';
                ctx.font = '500 34px ui-monospace, Menlo, Consolas, monospace';
                ctx.fillText(url.replace(/^https?:\/\//, ''), W - 70, fy, 640);
            }

            var dhLogoReady = load(DH_LOGO);
            document.querySelectorAll('canvas[data-qr]').forEach(function (canvas) {
                Promise.all([load(canvas.getAttribute('data-qr-logo')), dhLogoReady]).then(function (imgs) {
                    canvas._logos = imgs;
                    drawSign(canvas, imgs[0], imgs[1]);
                });
            });

            function save(canvas, name) {
                var a = document.createElement('a');
                a.href = canvas.toDataURL('image/png');
                a.download = name + '.png';
                document.body.appendChild(a); a.click(); a.remove();
            }
            document.querySelectorAll('[data-qr-download]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var canvas = document.querySelector('#' + btn.getAttribute('data-qr-download') + ' canvas');
                    save(canvas, (canvas.getAttribute('data-qr-name') || 'divers-hub') + '-sign');
                });
            });
            // The code alone, for a shop's own flyers, cards and receipts.
            document.querySelectorAll('[data-qr-only]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var canvas = document.querySelector('#' + btn.getAttribute('data-qr-only') + ' canvas');
                    var only = document.createElement('canvas');
                    only.width = only.height = 1000;
                    drawCode(only.getContext('2d'), canvas.getAttribute('data-qr'), 0, 0, 1000, (canvas._logos || [])[1]);
                    save(only, (canvas.getAttribute('data-qr-name') || 'divers-hub') + '-qr');
                });
            });
            // Print one sign: mark it, print, unmark. The print styles hide
            // everything on the page except the marked sign.
            document.querySelectorAll('[data-qr-print]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var sign = document.getElementById(btn.getAttribute('data-qr-print'));
                    document.body.classList.add('dh-printing-sign');
                    sign.classList.add('is-printing');
                    var done = function () { document.body.classList.remove('dh-printing-sign'); sign.classList.remove('is-printing'); window.removeEventListener('afterprint', done); };
                    window.addEventListener('afterprint', done);
                    window.print();
                });
            });
            window.dhDrawCode = drawCode;   // for checking the drawing against the generator
        })();
    </script>
    @endif

    <script>
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-copy'));
                input.select();
                (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject()).catch(function () { document.execCommand('copy'); });
                btn.textContent = 'Copied';
            });
        });
    </script>
</x-shop.page>
