<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Newsletters" icon="mail" />

        <div class="container-fluid py-0 dh-board">

            {{--modal delete--}}
            <div class="modal fade" id="modal-delete-issue" data-backdrop="static" data-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-danger modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal">Remove draft</h6>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <i class="material-icons h1 text-danger">warning</i>
                                <h4 id="deleteIssueConfirmText" class="text-gradient text-info mt-4">Remove this draft?</h4>
                                <p class="text-sm text-secondary">This can't be undone.</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form id="deleteIssueForm" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn bg-gradient-danger ms-auto">Remove draft</button>
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
            @if(session('error'))
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">error</span>
                    {{ session('error') }}
                </p>
            @endif

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">Newsletters</h2>
                <a class="dh-btn dh-btn-primary" href="{{ route('Newsletter.manage.create') }}">
                    <span class="material-icons-round" aria-hidden="true">add</span>New issue
                </a>
            </div>

            <div class="dh-admin-card">
                <div class="table-responsive">
                    <table class="dh-admin-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Sent</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($issues as $issue)
                                <tr>
                                    <td><b>{{ $issue->subject }}</b></td>
                                    <td>
                                        <span class="chip {{ $issue->isSent() ? 'chip-yes' : 'chip-static' }}">{{ ucfirst($issue->status) }}</span>
                                    </td>
                                    <td>{{ $issue->author->name ?? 'Unknown' }}</td>
                                    <td>
                                        @if($issue->isSent())
                                            {{ $issue->sent_count }} on {{ $issue->sent_at->format('M j, Y') }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                    <td>{{ $issue->updated_at->format('M j, Y') }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <a href="{{ route('Newsletter.manage.preview', $issue) }}" title="Preview" target="_blank" rel="noopener"><i class="material-icons" style="font-size:20px;">visibility</i></a>
                                            @unless($issue->isSent())
                                                <a href="{{ route('Newsletter.manage.edit', $issue) }}" title="Edit"><i class="material-icons" style="font-size:20px;">edit</i></a>
                                                <button type="button" class="is-danger" title="Remove" onclick="confirmDeleteIssue('{{ route('Newsletter.manage.destroy', $issue) }}', '{{ addslashes($issue->subject) }}')"><i class="material-icons" style="font-size:20px;">delete</i></button>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="6">No newsletters yet. <a href="{{ route('Newsletter.manage.create') }}">Write the first one</a>.</td>
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
        function confirmDeleteIssue(url, subject) {
            document.getElementById('deleteIssueConfirmText').textContent = 'Remove "' + subject + '"?';
            document.getElementById('deleteIssueForm').action = url;
            new bootstrap.Modal(document.getElementById('modal-delete-issue')).show();
        }
    </script>
    @endpush
</x-page-template>
