{{--
    Gas Blending (Pablo, 2026-10-17): 3 blending calculators on one page -
    Blend Standard Trimix, Top Off an existing mix, Bleed Down an existing
    mix. A separate "Blend Helitrox" tool was folded back into Trimix
    (normoxic mixes are just target-gas presets there now - see
    config/gas_blends.php's own comment) once Trimix's configurable bank
    gas made it strictly more capable with a better UI. All pure
    client-side arithmetic (public/assets/js/gas-blending.js), open to
    every registered user (no admin/personal gate, matching GasPlanning).
    The tab switcher and preset chips reuse the same
    .dh-channel-picker/.dh-channel-chip idiom DivePlanner.blade.php uses
    for its Single/Multi Level and OC/CC switches - there's no other
    content-area tab component in this app.
--}}
<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Gas Blending" icon="colorize" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">

                    <div class="dh-channel-picker" id="gasBlendingTabs">
                        <button type="button" class="dh-channel-chip is-active" data-blend-tag="trimix">Blend Trimix/Nitrox</button>
                        <button type="button" class="dh-channel-chip" data-blend-tag="topoff">Top Off</button>
                        <button type="button" class="dh-channel-chip" data-blend-tag="bleed">Bleed Down</button>
                    </div>

                    {{-- ============================= Tool 1: Blend Standard Trimix ============================= --}}
                    <div class="dh-blend-panel" data-blend-panel="trimix">
                        {{-- Inputs card collapses (same dh-calc-inputs-head/chevron/hint pattern
                             as Deco Planner's #dh-deco-inputs-card, DivePlanner.blade.php) once a
                             calculation lands, and re-expands the moment any input changes -
                             inputsCardControllers.trimix in gas-blending.js (same controller
                             pattern reused by Top Off and Bleed Down below). --}}
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="trimixInputsCard">
                            <div class="dh-calc-inputs-head" id="trimixInputsToggle" role="button" tabindex="0" aria-expanded="true" aria-controls="trimixInputsBody">
                                <span class="material-icons-round" aria-hidden="true">colorize</span>
                                {{-- id'd so prefillFromQueryString() in gas-blending.js can rewrite
                                     this to "Blend Standard Trimix 18/45" or "Blend Standard EAN32"
                                     when arriving from a Blend Mix button elsewhere (Pablo,
                                     2026-10-17: "add the mix if the page is called from any of the
                                     sources... depending if the gas was a trimix or a nitrox"). --}}
                                <h3 id="trimixInputsTitle">Blend Standard Trimix/Nitrox</h3>
                                <span class="dh-calc-inputs-hint" id="trimixInputsHint" hidden>Tap to edit</span>
                                <span class="material-icons-round dh-calc-inputs-chevron" id="trimixInputsChevron" aria-hidden="true">expand_less</span>
                            </div>
                            <div class="card-body" id="trimixInputsBody">
                                <p class="text-secondary text-sm">Blend up to any target O2/He mix using pure oxygen, pure helium and a bank/topping gas. Leave the starting gas at 0 psi for a fresh/empty cylinder, or fill it in to blend up from whatever's already in the tank.</p>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Starting gas</div>
                                    <x-gas-slider-field id="trimixStartPressure" label="Pressure" unit="psi" :value="0" :min="0" :max="5000" :step="1" />
                                    <x-gas-slider-field id="trimixStartO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="trimixStartHe" label="He%" :value="0" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Target gas</div>
                                    {{-- 3 explicit rows, not one wrapping row (Pablo, 2026-10-17:
                                         "in mobile view...show the chips for target in 3 rows -
                                         ean32/ean36, all helitrox, all trimix") - grouped by each
                                         preset's own `group` tag (config/gas_blends.php), not a
                                         computed filter: 21/35 and 21/21 both have o2=21 but
                                         belong in different rows, so o2/he math alone can't tell
                                         nitrox/helitrox/trimix apart. --}}
                                    @php
                                        $trimixPresets = collect(config('gas_blends.trimix'))->groupBy('group');
                                        $groupLabels = ['nitrox' => 'Nitrox', 'helitrox' => 'Helitrox', 'trimix' => 'Trimix'];
                                    @endphp
                                    @foreach ($groupLabels as $groupKey => $groupLabel)
                                        <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                                            <span class="dh-gas-label" style="flex: 0 0 60px; margin-bottom: 0;">{{ $groupLabel }}</span>
                                            <div class="dh-channel-picker">
                                                @foreach ($trimixPresets[$groupKey] ?? [] as $preset)
                                                    <x-gas-chip :o2="$preset['o2']" :he="$preset['he']" :label="$preset['he'] == 0 ? $preset['label'] : null" fill-o2="trimixTargetO2" fill-he="trimixTargetHe" />
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <x-gas-slider-field id="trimixTargetPressure" label="Pressure" unit="psi" :value="3000" :min="0" :max="5000" :step="1" />
                                    <x-gas-slider-field id="trimixTargetO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="trimixTargetHe" label="He%" :value="35" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Topping/bank gas</div>
                                    <div class="dh-channel-picker mb-2" id="trimixBankChips">
                                        @foreach (config('gas_blends.bank') as $i => $preset)
                                            <x-gas-chip :o2="$preset['o2']" :he="$preset['he']" :label="$preset['he'] == 0 ? $preset['label'] : null" fill-o2="trimixBankO2" fill-he="trimixBankHe" :active="$i === 0" />
                                        @endforeach
                                    </div>
                                    <x-gas-slider-field id="trimixBankO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="trimixBankHe" label="He%" :value="0" />
                                </div>

                                <div class="text-center"><button type="button" class="dh-btn dh-btn-primary" id="trimixCalcBtn">Calculate</button></div>

                                <div id="trimixUnblendable" class="dh-health-errored" hidden>
                                    <img src="{{ asset('assets') }}/img/unblendable_sign.png" alt="" style="width: 18px; height: 18px; flex: 0 0 auto;">
                                    This target mix can't be blended from this starting gas using O2 + He + the selected topping gas alone.
                                </div>
                            </div>
                        </div>

                        {{-- Separate results card (Pablo, 2026-10-17: "show the results in a
                             different card, like we did the deco planner") - hidden until a
                             calculation succeeds, re-hidden the moment any input changes again.
                             Populated by renderSteps('trimix', ...) in gas-blending.js - one
                             draggable row per gas added (He / O2 / topping, reorder order is
                             arbitrary here, unlike Top Off/Bleed Down below), each showing the
                             running tank pressure before and after that step. --}}
                        <div id="trimixResultsCard" style="display: none;">
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="dh-calc-inputs-head">
                                    <span class="material-icons-round" aria-hidden="true">checklist</span>
                                    <h3>Blend Result</h3>
                                </div>
                                <div class="card-body">
                                    {{-- Tank + stacked-bar fill + 3-part O2/He/N2 pill, same
                                         markup/behavior as Best Gases (GasPlanning.blade.php:513-548,
                                         ::updateGasMix()) - Pablo, 2026-10-17: "show the template of
                                         the tank that we use in best gases...with the bar chart
                                         rendering in the background...split pill with 3 parts...
                                         below the tank". Driven by the TARGET mix, updated via
                                         updateTankMix('trimix', ...) alongside renderSteps(). --}}
                                    <div class="dh-gas-tank-layout" style="justify-content: center;">
                                        <div class="dh-gas-tank-graphic">
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 105px; height: 210px;">
                                                <img src="{{ asset('assets') }}/img/tank_single.png" alt="" style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">
                                                <div style="width: 210px; height: 141px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="trimixStackedBarChart" style="width: 100%; height: 141px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="trimixMixO2">21%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-he" id="trimixMixHe" hidden>0%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="trimixMixN2">79%</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-secondary text-xs mb-2 mt-3">Drag to reorder the fill sequence.</p>
                                    <div class="dh-blend-steps" id="trimixSteps"></div>

                                    {{-- Up to 3 dive shops that can provide this fill (Pablo,
                                         2026-10-17: "suggest the local dive shops that can provide
                                         this fills") - filtered in JS by onSiteFillNitrox/Trimix
                                         depending on whether this blend needs helium, from the
                                         candidates GasBlendingController::operatorCandidates()
                                         built off the user's favorite operators/locations. Hidden
                                         entirely when there's nothing to suggest (no favorites, or
                                         none of them offer this fill type). --}}
                                    <div id="trimixOperatorSuggestions" class="mt-3" hidden>
                                        <div class="dh-gas-label">Dive shops that can fill this</div>
                                        <div class="dh-channel-picker" id="trimixOperatorChips"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================= Tool 3: Top Off an Existing Mix ============================= --}}
                    <div class="dh-blend-panel" data-blend-panel="topoff" hidden>
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="topoffInputsCard">
                            <div class="dh-calc-inputs-head" id="topoffInputsToggle" role="button" tabindex="0" aria-expanded="true" aria-controls="topoffInputsBody">
                                <span class="material-icons-round" aria-hidden="true">colorize</span>
                                <h3>Top Off an Existing Mix</h3>
                                <span class="dh-calc-inputs-hint" id="topoffInputsHint" hidden>Tap to edit</span>
                                <span class="material-icons-round dh-calc-inputs-chevron" id="topoffInputsChevron" aria-hidden="true">expand_less</span>
                            </div>
                            <div class="card-body" id="topoffInputsBody">
                                <p class="text-secondary text-sm">You have a known mix in the tank and you're topping it off with a known gas (air, or another known mix/bank). This calculates the resulting mix - nothing to solve for, just a weighted average.</p>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Gas currently in the tank</div>
                                    <x-gas-slider-field id="topoffStartPressure" label="Pressure" unit="psi" :value="1000" :min="0" :max="5000" :step="1" />
                                    <x-gas-slider-field id="topoffStartO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="topoffStartHe" label="He%" :value="35" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Topping gas</div>
                                    <div class="dh-channel-picker mb-2" id="topoffGasChips">
                                        @foreach (config('gas_blends.bank') as $preset)
                                            <x-gas-chip :o2="$preset['o2']" :he="$preset['he']" :label="$preset['he'] == 0 ? $preset['label'] : null" fill-o2="topoffGasO2" fill-he="topoffGasHe" :active="$preset['label'] === 'Air'" />
                                        @endforeach
                                    </div>
                                    <x-gas-slider-field id="topoffGasO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="topoffGasHe" label="He%" :value="0" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Fill to</div>
                                    <x-gas-slider-field id="topoffTargetPressure" label="Final pressure" unit="psi" :value="3000" :min="0" :max="5000" :step="1" />
                                </div>

                                <div class="text-center"><button type="button" class="dh-btn dh-btn-primary" id="topoffCalcBtn">Calculate</button></div>

                                <div id="topoffUnblendable" class="dh-health-errored" hidden>Final pressure must be higher than the tank's current pressure.</div>
                            </div>
                        </div>

                        <div id="topoffResultsCard" style="display: none;">
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="dh-calc-inputs-head">
                                    <span class="material-icons-round" aria-hidden="true">checklist</span>
                                    <h3>Top Off Result</h3>
                                </div>
                                <div class="card-body">
                                    <div class="dh-gas-tank-layout" style="justify-content: center;">
                                        <div class="dh-gas-tank-graphic">
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 105px; height: 210px;">
                                                <img src="{{ asset('assets') }}/img/tank_single.png" alt="" style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">
                                                <div style="width: 210px; height: 141px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="topoffStackedBarChart" style="width: 100%; height: 141px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="topoffMixO2">21%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-he" id="topoffMixHe" hidden>0%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="topoffMixN2">79%</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="dh-blend-steps mt-3" id="topoffSteps"></div>

                                    <div id="topoffOperatorSuggestions" class="mt-3" hidden>
                                        <div class="dh-gas-label">Dive shops that can fill this</div>
                                        <div class="dh-channel-picker" id="topoffOperatorChips"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============================= Tool 4: Bleed Down an Existing Mix ============================= --}}
                    <div class="dh-blend-panel" data-blend-panel="bleed" hidden>
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="bleedInputsCard">
                            <div class="dh-calc-inputs-head" id="bleedInputsToggle" role="button" tabindex="0" aria-expanded="true" aria-controls="bleedInputsBody">
                                <span class="material-icons-round" aria-hidden="true">colorize</span>
                                <h3>Bleed Down an Existing Mix</h3>
                                <span class="dh-calc-inputs-hint" id="bleedInputsHint" hidden>Tap to edit</span>
                                <span class="material-icons-round dh-calc-inputs-chevron" id="bleedInputsChevron" aria-hidden="true">expand_less</span>
                            </div>
                            <div class="card-body" id="bleedInputsBody">
                                <p class="text-secondary text-sm">You have the wrong mix in the tank. This calculates the pressure to vent down to before topping back up with a correction gas, so you land on the target mix with minimal wasted gas. Not every target is reachable with a single correction gas - if it isn't, try a different correction gas or use Blend Trimix/Nitrox instead.</p>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Gas currently in the tank</div>
                                    <x-gas-slider-field id="bleedStartPressure" label="Pressure" unit="psi" :value="3000" :min="0" :max="5000" :step="1" />
                                    <x-gas-slider-field id="bleedStartO2" label="O2%" :value="18" />
                                    <x-gas-slider-field id="bleedStartHe" label="He%" :value="45" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Target gas</div>
                                    @php
                                        $bleedTrimixPresets = collect(config('gas_blends.trimix'))->groupBy('group');
                                    @endphp
                                    @foreach ($groupLabels as $groupKey => $groupLabel)
                                        <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                                            <span class="dh-gas-label" style="flex: 0 0 60px; margin-bottom: 0;">{{ $groupLabel }}</span>
                                            <div class="dh-channel-picker">
                                                @foreach ($bleedTrimixPresets[$groupKey] ?? [] as $preset)
                                                    <x-gas-chip :o2="$preset['o2']" :he="$preset['he']" :label="$preset['he'] == 0 ? $preset['label'] : null" fill-o2="bleedTargetO2" fill-he="bleedTargetHe" />
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <x-gas-slider-field id="bleedTargetPressure" label="Final pressure" unit="psi" :value="3000" :min="0" :max="5000" :step="1" />
                                    <x-gas-slider-field id="bleedTargetO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="bleedTargetHe" label="He%" :value="35" />
                                </div>

                                <div class="dh-level-panel mb-3">
                                    <div class="fw-bold mb-2">Correction gas (used to top back up after bleeding)</div>
                                    <div class="dh-channel-picker mb-2" id="bleedGasChips">
                                        @foreach (config('gas_blends.bank') as $preset)
                                            <x-gas-chip :o2="$preset['o2']" :he="$preset['he']" :label="$preset['he'] == 0 ? $preset['label'] : null" fill-o2="bleedGasO2" fill-he="bleedGasHe" :active="$preset['label'] === 'Air'" />
                                        @endforeach
                                    </div>
                                    <x-gas-slider-field id="bleedGasO2" label="O2%" :value="21" />
                                    <x-gas-slider-field id="bleedGasHe" label="He%" :value="0" />
                                </div>

                                <div class="text-center"><button type="button" class="dh-btn dh-btn-primary" id="bleedCalcBtn">Calculate</button></div>

                                <div id="bleedUnblendable" class="dh-health-errored" hidden>
                                    <img src="{{ asset('assets') }}/img/unblendable_sign.png" alt="" style="width: 18px; height: 18px; flex: 0 0 auto;">
                                    This target isn't reachable by bleeding down and topping with this correction gas alone - try a different correction gas, or use Blend Trimix/Nitrox instead.
                                </div>
                            </div>
                        </div>

                        <div id="bleedResultsCard" style="display: none;">
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="dh-calc-inputs-head">
                                    <span class="material-icons-round" aria-hidden="true">checklist</span>
                                    <h3>Bleed Down Result</h3>
                                </div>
                                <div class="card-body">
                                    <div class="dh-gas-tank-layout" style="justify-content: center;">
                                        <div class="dh-gas-tank-graphic">
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 105px; height: 210px;">
                                                <img src="{{ asset('assets') }}/img/tank_single.png" alt="" style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">
                                                <div style="width: 210px; height: 141px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="bleedStackedBarChart" style="width: 100%; height: 141px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="bleedMixO2">21%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-he" id="bleedMixHe" hidden>0%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="bleedMixN2">79%</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Not draggable (unlike Blend Trimix/Nitrox's 3 rows) - bleed-then-
                                         refill is a fixed physical order, not an arbitrary display
                                         choice, so there's nothing to reorder. --}}
                                    <div class="dh-blend-steps mt-3" id="bleedSteps"></div>

                                    <div id="bleedOperatorSuggestions" class="mt-3" hidden>
                                        <div class="dh-gas-label">Dive shops that can fill this</div>
                                        <div class="dh-channel-picker" id="bleedOperatorChips"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
    <script>
        // GasBlendingController::operatorSuggestions() - {nitrox:[...],
        // trimix:[...]}, each already resolved to exactly 3 (favorites
        // first) or empty for a guest - read by renderOperatorSuggestions()
        // in gas-blending.js.
        window.dhOperatorSuggestions = @json($operatorSuggestions);
    </script>
    <script src="{{ asset('assets') }}/js/gas-blending.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
