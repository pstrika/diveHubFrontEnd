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
                    <p class="dh-wizard-lead">Same boats, same sites, a lot more personal. Four quick questions and the app starts working around how you dive: the boats you follow, the sites you can do, the weather where you launch.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" enctype="multipart/form-data" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="welcome">
                        <label class="dh-wizard-field">
                            <span>What should we call you?</span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name">
                        </label>
                        <label class="dh-wizard-field dh-wizard-photo">
                            <span>A photo for your profile <small>(optional)</small></span>
                            <span class="dh-wizard-photo-row">
                                @if($user->picture)
                                    <img src="{{ asset('assets') }}/img/users/{{ $user->picture }}" alt="" width="56" height="56">
                                @else
                                    <span class="material-icons-round" aria-hidden="true">account_circle</span>
                                @endif
                                <input type="file" name="picture" accept="image/*">
                            </span>
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
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ $stepUrl('comms') }}">Skip this</a>
                        </div>
                    </form>

                @elseif($step === 'comms')
                    <h1 class="dh-wizard-title">How should we reach you?</h1>
                    <p class="dh-wizard-lead">Only about diving: a reminder the day before a trip you saved, and what your groups are up to. Pick the ones you want and leave the rest.</p>
                    <form method="POST" action="{{ route('welcome.save') }}" class="dh-wizard-form">
                        @csrf
                        <input type="hidden" name="step" value="comms">
                        <label class="dh-wizard-field">
                            <span>Mobile number <small>(only needed for SMS or WhatsApp)</small></span>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" inputmode="tel">
                        </label>
                        <x-comms-preferences :user="$user" :ids="false" :showPhone="false" />
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
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('Trips') }}?range=weekend{{ $favOperators ? '&op=' . implode(',', $favOperators) : '' }}"><span class="material-icons-round">sailing</span>Boats this weekend</a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('MyGroups') }}"><span class="material-icons-round">groups</span>My groups</a>
                    </div>
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
