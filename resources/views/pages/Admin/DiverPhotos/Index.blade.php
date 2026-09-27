<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Picture Management" icon="photo_camera" />

        <div class="container-fluid py-0 dh-board">

            {{-- Full-size preview modal - a plain new-tab link left mobile
                 admins stuck with no way back (Pablo, 2026-09-24: "if I
                 click on the picture I get trapped and can never close
                 it... put it on a modal"). Same #dh-photo-modal pattern the
                 site page itself uses, with the close X. --}}
            <div class="modal fade dh-photo-modal" id="dh-photo-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <button type="button" class="dh-photo-modal-close" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                        <img id="dh-photo-modal-img" src="" alt="">
                    </div>
                </div>
            </div>

            {{-- Reject/remove confirm modal - one shared modal, its form action
                 swapped per row, same pattern as Blog Manage's delete confirm. --}}
            <div class="modal fade" id="modal-diver-photo-confirm" tabindex="-1">
                <div class="modal-dialog modal-danger modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="diverPhotoConfirmTitle">Confirm</h6>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <i class="material-icons h1 text-danger">warning</i>
                                <p id="diverPhotoConfirmText" class="text-sm text-secondary">This can't be undone.</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form id="diverPhotoConfirmForm" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="_method" id="diverPhotoConfirmMethod" value="POST">
                                        <button type="submit" class="btn bg-gradient-danger ms-auto" id="diverPhotoConfirmSubmit">Confirm</button>
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

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">Pending review <span class="dh-region-count">{{ $pending->count() }}</span></h2>
            </div>

            <div class="dh-admin-card mb-4">
                <div class="table-responsive">
                    <table class="dh-admin-table">
                        <thead>
                            <tr>
                                <th>Picture</th>
                                <th>Site</th>
                                <th>Uploaded by</th>
                                <th>Uploaded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $photo)
                                <tr>
                                    <td>
                                        <button type="button" style="padding:0;border:0;background:none;cursor:pointer;" data-bs-toggle="modal" data-bs-target="#dh-photo-modal"
                                            data-photo-src="{{ \App\Support\SitePhoto::web($photo->file) }}" data-photo-alt="Picture of {{ $photo->site->name ?? 'a site' }}">
                                            <img src="{{ \App\Support\SitePhoto::thumb($photo->file) }}" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:6px;">
                                        </button>
                                    </td>
                                    <td>
                                        @if($photo->site)
                                            <a href="{{ route('SiteDetails', $photo->site->slug ?? $photo->site->id) }}" target="_blank" rel="noopener">{{ $photo->site->name }}</a>
                                        @else
                                            <span class="text-secondary">Deleted site</span>
                                        @endif
                                    </td>
                                    <td>{{ $photo->user->name ?? 'Unknown' }}</td>
                                    <td>{{ $photo->created_at->format('M j, Y') }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <form method="POST" action="{{ route('DiverPhotos.manage.approve', $photo) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" title="Approve" style="color: var(--dh-good);"><i class="material-icons" style="font-size:20px;">check_circle</i></button>
                                            </form>
                                            <button type="button" class="is-danger" title="Reject"
                                                onclick="confirmDiverPhotoAction('{{ route('DiverPhotos.manage.reject', $photo) }}', 'POST', 'Reject this picture?', 'It will not show on the site page.', 'Reject')">
                                                <i class="material-icons" style="font-size:20px;">cancel</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="5">Nothing waiting for review.</td>
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
                                <th>Picture</th>
                                <th>Site</th>
                                <th>Uploaded by</th>
                                <th>Status</th>
                                <th>Reviewed by</th>
                                <th>Reviewed</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $photo)
                                <tr>
                                    <td>
                                        <button type="button" style="padding:0;border:0;background:none;cursor:pointer;" data-bs-toggle="modal" data-bs-target="#dh-photo-modal"
                                            data-photo-src="{{ \App\Support\SitePhoto::web($photo->file) }}" data-photo-alt="Picture of {{ $photo->site->name ?? 'a site' }}">
                                            <img src="{{ \App\Support\SitePhoto::thumb($photo->file) }}" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:6px;">
                                        </button>
                                    </td>
                                    <td>
                                        @if($photo->site)
                                            <a href="{{ route('SiteDetails', $photo->site->slug ?? $photo->site->id) }}" target="_blank" rel="noopener">{{ $photo->site->name }}</a>
                                        @else
                                            <span class="text-secondary">Deleted site</span>
                                        @endif
                                    </td>
                                    <td>{{ $photo->user->name ?? 'Unknown' }}</td>
                                    <td><span class="chip {{ $photo->status === 'approved' ? 'chip-yes' : 'chip-poor' }}">{{ ucfirst($photo->status) }}</span></td>
                                    <td>{{ $photo->reviewer->name ?? '—' }}</td>
                                    <td>{{ $photo->reviewedAt?->format('M j, Y') ?? '—' }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <button type="button" class="is-danger" title="Remove"
                                                onclick="confirmDiverPhotoAction('{{ route('DiverPhotos.manage.destroy', $photo) }}', 'DELETE', 'Remove this picture?', 'This deletes the file and can\'t be undone.', 'Remove')">
                                                <i class="material-icons" style="font-size:20px;">delete</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="7">No reviewed pictures yet.</td>
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
        document.getElementById('dh-photo-modal')?.addEventListener('show.bs.modal', function (e) {
            var img = document.getElementById('dh-photo-modal-img');
            img.src = e.relatedTarget.getAttribute('data-photo-src');
            img.alt = e.relatedTarget.getAttribute('data-photo-alt') || '';
        });

        function confirmDiverPhotoAction(url, method, title, text, submitLabel) {
            document.getElementById('diverPhotoConfirmTitle').textContent = title;
            document.getElementById('diverPhotoConfirmText').textContent = text;
            document.getElementById('diverPhotoConfirmForm').action = url;
            document.getElementById('diverPhotoConfirmMethod').value = method;
            document.getElementById('diverPhotoConfirmSubmit').textContent = submitLabel;
            new bootstrap.Modal(document.getElementById('modal-diver-photo-confirm')).show();
        }
    </script>
    @endpush
</x-page-template>
