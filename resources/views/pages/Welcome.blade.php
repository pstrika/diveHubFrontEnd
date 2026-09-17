<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Welcome" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Welcome wizard (OnboardingController). One step per page load, each a
                plain form. Big tappable choices, nothing to type except a name.
                Every step can be skipped; the whole thing can be skipped for two weeks.
            --}}
            @php
                $index = array_search($step, $steps, true);
                $stepUrl = fn ($s) => route('welcome', ['step' => $s]);
                $nextStep = $steps[$index + 1] ?? 'done';
                $isMember = auth()->user() && auth()->user()->isNotGuest();
            @endphp

            <div class="dh-wizard">
                <ol class="dh-wizard-dots" aria-label="Progress">
                    @foreach($steps as $i => $s)
                        @continue($s === 'done')
                        <li class="{{ $i < $index ? 'is-done' : ($i === $index ? 'is-current' : '') }}"><span class="visually-hidden">{{ $s }}</span></li>
                    @endforeach
                </ol>

                @if($step === 'welcome')
                    <h1 class="dh-wizard-title">Welcome to the new Divers Hub</h1>
                    <p class="dh-wizard-lead">You're in. With an account you can save trips, plan and log your dives, join dive groups, and get a reminder before a trip you've saved. A few quick questions and the app starts working around how and where you dive.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" enctype="multipart/form-data" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="welcome">
                        <label class="dh-wizard-field dh-wizard-photo">
                            <span>A photo for your profile <small>(optional)</small></span>
                            @if($user->picture)
                                <span class="dh-wizard-photo-row">
                                    <img src="{{ asset('assets') }}/img/users/{{ $user->picture }}" alt="" width="56" height="56">
                                    <input type="file" name="picture" accept="image/*">
                                </span>
                            @elseif($user->google_avatar_url)
                                {{-- Signed up with Google - offer their Google photo instead of
                                     only a blank upload field (Pablo, 2026-09-16: "if the user
                                     registered using Google SSO, we can ask them if they want to
                                     use their Google Profile Pic or upload a new one"). --}}
                                <div class="dh-wizard-choices dh-wizard-choices-compact">
                                    <label class="dh-choice">
                                        <input type="radio" name="photoSource" value="google" checked>
                                        <span class="dh-choice-body dh-choice-body-row">
                                            <img src="{{ $user->google_avatar_url }}" alt="" width="40" height="40" style="border-radius: 50%;">
                                            <span class="dh-choice-title">Use my Google photo</span>
                                        </span>
                                    </label>
                                    <label class="dh-choice">
                                        <input type="radio" name="photoSource" value="upload" id="dhPhotoUploadChoice">
                                        <span class="dh-choice-body dh-choice-body-row">
                                            <span class="material-icons-round" aria-hidden="true">upload</span>
                                            <span class="dh-choice-title">Upload a different photo</span>
                                        </span>
                                    </label>
                                </div>
                                <input type="file" name="picture" accept="image/*" id="dhPhotoUploadInput" hidden style="margin-top: 8px;">
                                <script>
                                    (function () {
                                        var uploadChoice = document.getElementById('dhPhotoUploadChoice');
                                        var uploadInput = document.getElementById('dhPhotoUploadInput');
                                        if (!uploadChoice || !uploadInput) return;
                                        document.querySelectorAll('input[name="photoSource"]').forEach(function (radio) {
                                            radio.addEventListener('change', function () {
                                                uploadInput.hidden = !uploadChoice.checked;
                                            });
                                        });
                                    })();
                                </script>
                            @else
                                <span class="dh-wizard-photo-row">
                                    <span class="material-icons-round" aria-hidden="true">account_circle</span>
                                    <input type="file" name="picture" accept="image/*">
                                </span>
                            @endif
                        </label>
                        @error('picture')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        <div class="dh-wizard-actions">
                            <button type="submit" class="dh-btn dh-btn-primary">Next</button>
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl($nextStep) }}">Skip this</a>
                        </div>
                    </form>

                @elseif($step === 'level')
                    <h1 class="dh-wizard-title">What is your certification level?</h1>
                    <p class="dh-wizard-lead">We use it to cap what we recommend. You can always browse everything.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="level">
                        <div class="dh-wizard-choices">
                            @foreach($levels as $value => $lvl)
                                <label class="dh-choice">
                                    <input type="radio" name="level" value="{{ $value }}" @if((string) $user->certLevel === (string) $value) checked @endif required>
                                    <span class="dh-choice-body">
                                        <x-dive-level.icon :level="$value" height="28" />
                                        <span class="dh-choice-title">{{ $lvl['name'] }}</span>
                                        <span class="dh-choice-sub">{{ $lvl['code'] }}@if($lvl['maxDepth']) · to {{ $lvl['maxDepth'] }} ft @endif</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('level')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                        <div class="dh-wizard-actions">
                            <button type="submit" class="dh-btn dh-btn-primary">Next</button>
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl($nextStep) }}">Skip this</a>
                        </div>
                    </form>

                @elseif($step === 'places')
                    <h1 class="dh-wizard-title">Where do you usually dive?</h1>
                    <p class="dh-wizard-lead">Pick as many as you like. Your dashboard shows conditions and boats for these first.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="places">
                        @foreach($locations as $coast => $locs)
                            <h2 class="dh-wizard-group">{{ $coast }}</h2>
                            <div class="dh-wizard-choices dh-wizard-choices-compact">
                                @foreach($locs as $loc)
                                    <label class="dh-choice">
                                        <input type="checkbox" name="locations[]" value="{{ $loc['id'] }}" @if(in_array($loc['id'], $favLocations, true)) checked @endif>
                                        <span class="dh-choice-body"><span class="dh-choice-title">{{ $loc['name'] }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="dh-wizard-actions">
                            <button type="submit" class="dh-btn dh-btn-primary">Next</button>
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl($nextStep) }}">Skip this</a>
                        </div>
                    </form>

                @elseif($step === 'operators')
                    <h1 class="dh-wizard-title">Any boats you dive with regularly?</h1>
                    <p class="dh-wizard-lead">Their calendars land on your dashboard and the trip finder gets a one tap "My favorites" filter.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="operators">
                        <div class="dh-wizard-choices dh-wizard-choices-compact">
                            @foreach($operators as $op)
                                <label class="dh-choice dh-choice-op">
                                    <input type="checkbox" name="operators[]" value="{{ $op->id }}" @if(in_array($op->id, $favOperators, true)) checked @endif>
                                    <span class="dh-choice-body">
                                        @if($op->logoUrl)<img src="{{ asset('assets') }}{{ $op->logoUrl }}" alt="" loading="lazy">@endif
                                        <span class="dh-choice-title">{{ $op->operatorName }}</span>
                                        <span class="dh-choice-sub">{{ $op->cityAddress }}@if($op->tec) · tech @endif</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <fieldset class="dh-wizard-field">
                            <legend>Weekend picks on your dashboard should follow</legend>
                            <label class="dh-radio"><input type="radio" name="recommendBy" value="locations" @if($user->prefersLocation || $user->prefersLocation === null) checked @endif> the places I picked</label>
                            <label class="dh-radio"><input type="radio" name="recommendBy" value="operators" @if($user->prefersLocation === 0 || $user->prefersLocation === false) checked @endif> the boats I picked</label>
                        </fieldset>
                        <div class="dh-wizard-actions">
                            <button type="submit" class="dh-btn dh-btn-primary">Next</button>
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl('phone') }}">Skip this</a>
                        </div>
                    </form>

                @elseif($step === 'phone')
                    @php
                        $isPending = !empty($user->pending_phone);
                        $isVerified = !empty($user->phone) && !empty($user->phone_verified_at);
                        $isIntlSaved = !empty($user->phone) && empty($user->phone_verified_at) && !$isPending;
                    @endphp
                    <h1 class="dh-wizard-title">Can we text you about your trips?</h1>
                    <p class="dh-wizard-lead">We'll only use this for trip reminders and last-minute schedule changes - never more than a few messages a month. Verify your number now and you can turn on SMS or WhatsApp reminders on the next step.</p>

                    @if(session('phoneError'))
                        {{-- Same visible warning-box treatment as the comms picker's own
                             notes, not easy-to-miss small red text - a diver reported
                             the send-code failure as "doing nothing" (Pablo, 2026-09-17),
                             which this alone doesn't fix (that's a real SMS delivery
                             problem), but it should at least be impossible to miss when
                             it does happen. --}}
                        <p class="dh-comms-note dh-comms-warn">
                            <span class="material-icons-round" aria-hidden="true">error_outline</span>
                            {{ session('phoneError') }}
                        </p>
                    @endif

                    @if($isPending)
                        <form method="POST" action="{{ route('profile.verifyPhone') }}" class="dh-wizard-form">
                            @csrf
                            <label class="dh-wizard-field">
                                <span>Enter the code we texted to {{ $user->pending_phone }}</span>
                                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
                            </label>
                            {{-- No "Skip this" here on purpose - once a code has actually
                                 been sent to a real number, that has to be verified (or
                                 abandoned via "Use a different number" below) rather than
                                 walked away from mid-flow (Pablo, 2026-09-16: "we need for
                                 the phone to be validated if the user didn't skip"). Skip
                                 is only available before entering a number at all, or by
                                 resetting back to that entry form. --}}
                            <div class="dh-wizard-actions">
                                <button type="submit" class="dh-btn dh-btn-primary">Verify</button>
                            </div>
                        </form>
                        <div class="dh-wizard-actions" style="margin-top: 4px;">
                            <form method="POST" action="{{ route('profile.resendPhoneCode') }}">
                                @csrf
                                <button type="submit" class="dh-link-btn">Resend code</button>
                            </form>
                            <form method="POST" action="{{ route('welcome.save') }}">
                                @csrf
                                <input type="hidden" name="step" value="phone">
                                <input type="hidden" name="resetPhone" value="1">
                                <button type="submit" class="dh-link-btn">Use a different number</button>
                            </form>
                        </div>
                    @elseif($isVerified)
                        <div class="dh-comms-row" style="display: flex; align-items: center; gap: 10px;">
                            <span class="material-icons-round" style="color: var(--dh-good);" aria-hidden="true">check_circle</span>
                            <span><strong>Verified:</strong> {{ $user->phone }}</span>
                        </div>
                        <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                            @csrf
                            <input type="hidden" name="step" value="phone">
                            <div class="dh-wizard-actions">
                                <button type="submit" class="dh-btn dh-btn-primary">Next</button>
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                            @csrf
                            <input type="hidden" name="step" value="phone">
                            <label class="dh-wizard-field">
                                <span>Mobile number</span>
                                <input type="tel" name="phone" value="{{ old('phone', $isIntlSaved ? $user->phone : '') }}" autocomplete="tel" inputmode="tel" placeholder="(555) 123-4567">
                            </label>
                            @if($isIntlSaved)
                                <p class="dh-comms-note">Saved for WhatsApp - international numbers can't receive our SMS verification codes.</p>
                            @endif
                            <div class="dh-wizard-actions">
                                <button type="submit" class="dh-btn dh-btn-primary">Send code</button>
                                <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl('comms') }}">Skip this</a>
                            </div>
                        </form>
                    @endif

                @elseif($step === 'comms')
                    <h1 class="dh-wizard-title">How should we reach you?</h1>
                    <p class="dh-wizard-lead">Only about diving: a reminder the day before a trip you saved, and what your groups are up to. Pick the ones you want and leave the rest.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="comms">
                        @if(!$user->phone)
                            <p class="dh-comms-note">Add a verified mobile number to enable SMS or WhatsApp - you can always do that later from your profile.</p>
                        @endif
                        <x-comms-preferences :user="$user" :ids="false" :showPhone="false" :forceUnchecked="true" />
                        <div class="dh-wizard-actions">
                            <button type="submit" class="dh-btn dh-btn-primary">Finish</button>
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl('done') }}">Skip this</a>
                        </div>
                    </form>

                @else
                    <h1 class="dh-wizard-title">You are set, {{ Str::before($user->name, ' ') }}</h1>
                    <p class="dh-wizard-lead">Everything you picked can be changed any time under My Profile. Here is where to start.</p>
                    <div class="dh-wizard-actions dh-wizard-actions-stack">
                        <a class="dh-btn dh-btn-primary" href="{{ route('MyDashboard') }}"><span class="material-icons-round">dashboard</span>My dashboard</a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('Trips') }}?range=weekend{{ $favOperators ? '&op=' . implode(',', $favOperators) : '' }}"><span class="material-icons-round">directions_boat</span>Boats this weekend</a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('MyGroups') }}"><span class="material-icons-round">groups</span>My groups</a>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dhTourOpen"><span class="material-icons-round">tour</span>Take a quick tour</button>
                    </div>

                    {{-- Brief illustrated tour, offered once the wizard is done (Pablo,
                         2026-09-16: "After this wizard is completed, we can offer a
                         brief 'tour' showing the app"). A self-contained slide carousel
                         rather than live on-page spotlights - it doesn't depend on
                         which page it's shown from or what state that page is in. --}}
                    <div class="dh-tour-backdrop" id="dhTourModal" hidden>
                        <div class="dh-tour" role="dialog" aria-modal="true" aria-labelledby="dhTourTitle">
                            <button type="button" class="dh-tour-close" id="dhTourClose" aria-label="Close tour">
                                <span class="material-icons-round" aria-hidden="true">close</span>
                            </button>
                            <ol class="dh-wizard-dots dh-tour-dots" id="dhTourDots" aria-hidden="true"></ol>
                            <div id="dhTourSlides">
                                <div class="dh-tour-slide">
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">dashboard</span>
                                    <h2 id="dhTourTitle" class="dh-wizard-title">Your dashboard</h2>
                                    <p class="dh-wizard-lead">Today's conditions, your upcoming trips, and picks built around the places and boats you chose - all in one place.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">scuba_diving</span>
                                    <h2 class="dh-wizard-title">Find a trip</h2>
                                    <p class="dh-wizard-lead">Browse boats by date, region or your favorite operators, filtered to what your certification allows.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">calendar_month</span>
                                    <h2 class="dh-wizard-title">Specialty calendars</h2>
                                    <p class="dh-wizard-lead">Dedicated calendars for lobster season, shark dives, wreck diving and technical trips, plus a whole section for beach diving - browse by what you're into, not just by date.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">directions_boat</span>
                                    <h2 class="dh-wizard-title">Dive operators</h2>
                                    <p class="dh-wizard-lead">A large, growing database of Florida dive operators - many with a direct link to their online waiver, so you're never hunting for it right before a trip.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">cloud</span>
                                    <h2 class="dh-wizard-title">Weather</h2>
                                    <p class="dh-wizard-lead">Sea state and forecasts for every coast, so you know before you launch.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">groups</span>
                                    <h2 class="dh-wizard-title">Groups</h2>
                                    <p class="dh-wizard-lead">Create or join a dive group, share a calendar, and keep everyone posted in one thread.</p>
                                </div>
                                <div class="dh-tour-slide" hidden>
                                    <span class="material-icons-round dh-tour-icon" aria-hidden="true">timer</span>
                                    <h2 class="dh-wizard-title">Dive tools</h2>
                                    <p class="dh-wizard-lead">Plan a dive minute by minute with the Deco Planner, or find your best gas mix with Best Gases.</p>
                                </div>
                            </div>
                            <div class="dh-wizard-actions">
                                <button type="button" class="dh-btn dh-btn-ghost-dark" id="dhTourPrev" hidden>Back</button>
                                <button type="button" class="dh-btn dh-btn-primary" id="dhTourNext">Next</button>
                            </div>
                        </div>
                    </div>
                    <script>
                        (function () {
                            var openBtn = document.getElementById('dhTourOpen');
                            var closeBtn = document.getElementById('dhTourClose');
                            var backdrop = document.getElementById('dhTourModal');
                            var slides = backdrop ? Array.prototype.slice.call(backdrop.querySelectorAll('.dh-tour-slide')) : [];
                            var dotsWrap = document.getElementById('dhTourDots');
                            var prevBtn = document.getElementById('dhTourPrev');
                            var nextBtn = document.getElementById('dhTourNext');
                            if (!openBtn || !backdrop || !slides.length) return;

                            slides.forEach(function () {
                                var dot = document.createElement('li');
                                dotsWrap.appendChild(dot);
                            });
                            var dots = Array.prototype.slice.call(dotsWrap.querySelectorAll('li'));
                            var index = 0;

                            function render() {
                                slides.forEach(function (slide, i) { slide.hidden = i !== index; });
                                dots.forEach(function (dot, i) {
                                    dot.classList.toggle('is-current', i === index);
                                    dot.classList.toggle('is-done', i < index);
                                });
                                prevBtn.hidden = index === 0;
                                nextBtn.textContent = index === slides.length - 1 ? 'Done' : 'Next';
                            }

                            function open() { index = 0; render(); backdrop.hidden = false; }
                            function close() { backdrop.hidden = true; }

                            openBtn.addEventListener('click', open);
                            closeBtn.addEventListener('click', close);
                            backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(); });
                            prevBtn.addEventListener('click', function () { if (index > 0) { index--; render(); } });
                            nextBtn.addEventListener('click', function () {
                                if (index < slides.length - 1) { index++; render(); } else { close(); }
                            });
                        })();
                    </script>
                @endif

                @if($step !== 'done')
                    <form method="POST" action="{{ route('welcome.skip') }}" class="dh-wizard-skip">
                        @csrf
                        <button type="submit" class="dh-link-btn">Skip for now, ask me in two weeks</button>
                    </form>
                @endif
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
