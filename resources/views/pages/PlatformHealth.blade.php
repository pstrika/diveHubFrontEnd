<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Platform Health" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Retheme (2026-09-11) onto the same flat .dh-card table style as
                the rest of the redesign, plus the crawler team's new operator
                status contract: _status is a signed integer code now (see
                App\Support\OperatorHealth), _runsRemaining tracks Fareharbor
                pagination, and _cron is the operator's Azure Function timer,
                rendered as a sentence. All parsing lives in OperatorHealth so
                this view stays a straight read of it - see that class's
                docblock for the rollout caveat on _status.
            --}}
            <section class="dh-card">
                <h2 class="dh-card-head">Operators scraping <span class="dh-region-count">{{ count($operators) }}</span></h2>
                <div class="dh-card-body dh-health-table-wrap">
                    <table class="dh-health-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th></th>
                                <th>Operator</th>
                                <th>Last run</th>
                                <th>Timestamp (ET)</th>
                                <th class="text-center">Trips added</th>
                                <th>Schedule (UTC)</th>
                                <th class="text-center">Runs remaining</th>
                                <th class="text-center">Version</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operators as $operator)
                                @php
                                    $utc = new \DateTime((string) $operator->_lastUpdate, new \DateTimeZone('UTC'));
                                    $et = (clone $utc)->setTimezone(new \DateTimeZone('America/New_York'));
                                    $interval = (new \DateTime())->diff($et);
                                    $ago = trim(
                                        ((int) $interval->format('%d') ? $interval->format('%d days ') : '')
                                        . ((int) $interval->format('%h') ? $interval->format('%h hrs ') : '')
                                        . ((int) $interval->format('%i') ? $interval->format('%i min') : '')
                                    );
                                    $status = \App\Support\OperatorHealth::status($operator->_status);
                                    $runsRemaining = \App\Support\OperatorHealth::runsRemainingLabel($operator->_runsRemaining, $operator->queryMaxDaySpan);
                                    $schedule = \App\Support\OperatorHealth::cronLabel($operator->_cron);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="dh-health-pill is-{{ $status['tone'] }}" title="_status = {{ $operator->_status }}">
                                            <span class="material-icons-round" aria-hidden="true">{{ $status['icon'] }}</span>{{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td>@if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="" class="dh-health-logo">@endif</td>
                                    <td class="dh-health-name">{{ $operator->operatorName }}</td>
                                    <td>{{ $ago !== '' ? $ago . ' ago' : 'just now' }}</td>
                                    <td>{{ $et->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">{{ $operator->_updatedCount }}</td>
                                    <td>{{ $schedule ?? '—' }}</td>
                                    <td class="text-center">{{ $runsRemaining ?? '—' }}</td>
                                    <td class="text-center">{{ $operator->_ver }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="dh-card">
                <h2 class="dh-card-head">Operators not scraping <span class="dh-region-count">{{ count($notScrapping) }}</span></h2>
                <div class="dh-card-body dh-health-table-wrap">
                    <table class="dh-health-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Operator</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notScrapping as $operator)
                                <tr>
                                    <td>@if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="" class="dh-health-logo">@endif</td>
                                    <td class="dh-health-name">{{ $operator->operatorName }}</td>
                                    <td>{{ $operator->location }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="dh-card">
                <h2 class="dh-card-head">Weather API <span class="dh-region-count">{{ count($weatherLocations) }}</span></h2>
                <div class="dh-card-body dh-health-table-wrap">
                    <table class="dh-health-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Last run</th>
                                <th>Timestamp (ET)</th>
                                <th class="text-center">Status code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weatherLocations as $weatherLocation)
                                @php
                                    $utc = new \DateTime((string) $weatherLocation->_lastUpdated, new \DateTimeZone('UTC'));
                                    $et = (clone $utc)->setTimezone(new \DateTimeZone('America/New_York'));
                                    $interval = (new \DateTime())->diff($et);
                                    $ago = trim(
                                        ((int) $interval->format('%d') ? $interval->format('%d days ') : '')
                                        . ((int) $interval->format('%h') ? $interval->format('%h hrs ') : '')
                                        . ((int) $interval->format('%i') ? $interval->format('%i min') : '')
                                    );
                                    // The weather crawler is still on the old 0/1 contract - not
                                    // part of the operators status rollout above.
                                    $wxTone = $weatherLocation->_status == '1' ? 'good' : ($weatherLocation->_status == '0' ? 'wait' : 'poor');
                                    $wxIcon = $weatherLocation->_status == '1' ? 'check_circle' : ($weatherLocation->_status == '0' ? 'schedule' : 'error');
                                @endphp
                                <tr>
                                    <td><span class="dh-health-pill is-{{ $wxTone }}"><span class="material-icons-round" aria-hidden="true">{{ $wxIcon }}</span></span></td>
                                    <td class="dh-health-name">{{ ucwords($weatherLocation->location) }}</td>
                                    <td>{{ $ago !== '' ? $ago . ' ago' : 'just now' }}</td>
                                    <td>{{ $et->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-center">{{ $weatherLocation->_status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
