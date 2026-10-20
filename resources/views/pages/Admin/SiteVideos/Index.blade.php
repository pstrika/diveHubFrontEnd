<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Picture Management" icon="photo_camera" />

        <div class="container-fluid py-0 dh-board">

            {{-- Videos tab of the Picture Management console (Pablo, 2026-10-20):
                 the same approve / reject / remove flow as diver pictures, for
                 videos uploaded on a site page or shared from a logbook dive.
                 Playback here uses an owner-style token, so pending (still
                 private) videos play for the admin. --}}
            <div class="modal fade dh-site-video-modal" id="dh-video-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <button type="button" class="dh-photo-modal-close" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                        <iframe id="dh-video-modal-frame" src="about:blank" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture" allowfullscreen title="Video"></iframe>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-site-video-confirm" tabindex="-1">
                <div class="modal-dialog modal-danger modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="siteVideoConfirmTitle">Confirm</h6>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <i class="material-icons h1 text-danger">warning</i>
                                <p id="siteVideoConfirmText" class="text-sm text-secondary">This can't be undone.</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form id="siteVideoConfirmForm" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="_method" id="siteVideoConfirmMethod" value="POST">
                                        <button type="submit" class="btn bg-gradient-danger ms-auto" id="siteVideoConfirmSubmit">Confirm</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <p class="dh-comms-note dh-comms-warn" style="background: var(--dh-good-bg); color: var(--dh-good);">
                    <span class="material-icons-round" aria-hidden="true">check_circle</span>
                    {{ session('success') }}
                </p>
            @endif

            <nav class="dh-sp-tabs mb-3" aria-label="Picture type">
                <a href="{{ route('DiverPhotos.manage.index') }}" class="{{ request()->routeIs('DiverPhotos.manage.index') ? 'is-active' : '' }}">Diver site photos</a>
                <a href="{{ route('Species.photoReview') }}" class="{{ request()->routeIs('Species.photoReview') ? 'is-active' : '' }}">Species pictures</a>
                <a href="{{ route('SiteVideos.manage.index') }}" class="{{ request()->routeIs('SiteVideos.manage.index') ? 'is-active' : '' }}">Videos</a>
            </nav>

            @php
                $thumbCell = function ($video) {
                    $thumb = $video->isReady() ? $video->thumbnailUrl(180) : null;
                    $player = $video->isReady() ? $video->playerUrl() : null;
                    if ($player) {
                        return '<button type="button" style="padding:0;border:0;background:none;cursor:pointer;position:relative;" data-bs-toggle="modal" data-bs-target="#dh-video-modal" data-player="' . e($player) . '">'
                            . '<img src="' . e($thumb) . '" alt="" style="width:85px;height:48px;object-fit:cover;border-radius:6px;background:#000;">'
                            . ($video->durationLabel() ? '<span class="dh-dive-video-duration">' . e($video->durationLabel()) . '</span>' : '')
                            . '</button>';
                    }
                    $label = ['uploading' => 'Uploading…', 'processing' => 'Processing…', 'failed' => 'Failed'][$video->stream_status] ?? $video->stream_status;
                    return '<span class="chip ' . ($video->stream_status === 'failed' ? 'chip-poor' : '') . '" title="' . e($video->stream_error ?? '') . '">' . e($label) . '</span>';
                };
            @endphp

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">Pending review <span class="dh-region-count">{{ $pending->count() }}</span></h2>
            </div>

            <div class="dh-admin-card mb-4">
                <div class="table-responsive">
                    <table class="dh-admin-table">
                        <thead>
                            <tr>
                                <th>Video</th>
                                <th>Site</th>
                                <th>Uploaded by</th>
                                <th>From</th>
                                <th>Uploaded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $video)
                                <tr>
                                    <td>{!! $thumbCell($video) !!}</td>
                                    <td>
                                        @if($video->site)
                                            <a href="{{ route('SiteDetails') }}/{{ $video->site->slug ?? $video->site->id }}" target="_blank" rel="noopener">{{ $video->site->name }}</a>
                                        @else
                                            <span class="text-secondary">Deleted site</span>
                                        @endif
                                    </td>
                                    <td>{{ $video->user->name ?? 'Unknown' }}</td>
                                    <td>{{ $video->owns_stream ? 'Site page' : 'Their logbook' }}</td>
                                    <td>{{ $video->created_at->format('M j, Y') }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <form method="POST" action="{{ route('SiteVideos.manage.approve', $video) }}" class="dh-diver-photo-approve-form">
                                                @csrf
                                                <input type="text" name="desc" placeholder="Short description" maxlength="150" class="dh-diver-photo-desc-input" value="{{ $video->desc }}">
                                                <button type="submit" title="Approve" style="color: var(--dh-good);" @disabled(!$video->isReady())><i class="material-icons" style="font-size:20px;">check_circle</i></button>
                                            </form>
                                            <button type="button" class="is-danger" title="Reject"
                                                onclick="confirmSiteVideoAction('{{ route('SiteVideos.manage.reject', $video) }}', 'POST', 'Reject this video?', 'It will not show on the site page.', 'Reject')">
                                                <i class="material-icons" style="font-size:20px;">cancel</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="6">Nothing waiting for review.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">History <span class="dh-region-count">{{ $history->count() }}</span></h2>
            </div>

            <div class="dh-admin-card">
                <div class="table-responsive">
                    <table class="dh-admin-table">
                        <thead>
                            <tr>
                                <th>Video</th>
                                <th>Site</th>
                                <th>Uploaded by</th>
                                <th>Status</th>
                                <th>Reviewed by</th>
                                <th>Reviewed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $video)
                                <tr>
                                    <td>{!! $thumbCell($video) !!}</td>
                                    <td>
                                        @if($video->site)
                                            <a href="{{ route('SiteDetails') }}/{{ $video->site->slug ?? $video->site->id }}" target="_blank" rel="noopener">{{ $video->site->name }}</a>
                                        @else
                                            <span class="text-secondary">Deleted site</span>
                                        @endif
                                    </td>
                                    <td>{{ $video->user->name ?? 'Unknown' }}</td>
                                    <td><span class="chip {{ $video->status === 'approved' ? 'chip-yes' : 'chip-poor' }}">{{ ucfirst($video->status) }}</span></td>
                                    <td>{{ $video->reviewer->name ?? '—' }}</td>
                                    <td>{{ $video->reviewedAt?->format('M j, Y') ?? '—' }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <button type="button" class="is-danger" title="Remove"
                                                onclick="confirmSiteVideoAction('{{ route('SiteVideos.manage.destroy', $video) }}', 'DELETE', 'Remove this video from the site page?', {{ json_encode($video->owns_stream ? 'This deletes the video and can\'t be undone.' : 'It stays in the diver\'s own logbook.') }}, 'Remove')">
                                                <i class="material-icons" style="font-size:20px;">delete</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="7">No reviewed videos yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script>
        (function () {
            var m = document.getElementById('dh-video-modal'), f = document.getElementById('dh-video-modal-frame');
            m?.addEventListener('show.bs.modal', function (e) { f.src = e.relatedTarget.getAttribute('data-player'); });
            m?.addEventListener('hidden.bs.modal', function () { f.src = 'about:blank'; });
        })();

        function confirmSiteVideoAction(url, method, title, text, submitLabel) {
            document.getElementById('siteVideoConfirmTitle').textContent = title;
            document.getElementById('siteVideoConfirmText').textContent = text;
            document.getElementById('siteVideoConfirmForm').action = url;
            document.getElementById('siteVideoConfirmMethod').value = method;
            document.getElementById('siteVideoConfirmSubmit').textContent = submitLabel;
            new bootstrap.Modal(document.getElementById('modal-site-video-confirm')).show();
        }
    </script>
    @endpush
</x-page-template>
