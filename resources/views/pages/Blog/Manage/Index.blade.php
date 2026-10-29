<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Articles" icon="auto_stories" />

        <div class="container-fluid py-0 dh-board">

            {{--modal delete--}}
            <div class="modal fade" id="modal-delete-post" data-backdrop="static" data-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-danger modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal">Remove article</h6>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <i class="material-icons h1 text-danger">warning</i>
                                <h4 id="deletePostConfirmText" class="text-gradient text-info mt-4">Remove this article?</h4>
                                <p class="text-sm text-secondary">This can't be undone.</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form id="deletePostForm" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn bg-gradient-danger ms-auto">Remove article</button>
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

            {{-- Review queue (2026-10-29): Creators' submitted articles, for an Admin to publish or send back. --}}
            @php $pendingPosts = auth()->user()->isAdmin() ? $posts->where('status', 'pending') : collect(); @endphp
            @if ($pendingPosts->isNotEmpty())
                <div class="dh-panel-head-row mb-3">
                    <h2 class="dh-panel-title mb-0">Waiting for review &middot; {{ $pendingPosts->count() }}</h2>
                </div>
                <div class="dh-admin-card mb-4 dh-review-queue">
                    @foreach ($pendingPosts as $post)
                        <div class="dh-review-item">
                            <div class="dh-review-item-main">
                                <b>{{ $post->title }}</b>
                                <span class="text-sm text-secondary">by {{ $post->author->name ?? 'Unknown' }} &middot; {{ $post->category }} &middot; submitted {{ $post->updated_at->format('M j, Y') }}</span>
                            </div>
                            <div class="dh-review-item-actions">
                                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('Blog.manage.edit', $post) }}"><span class="material-icons-round" aria-hidden="true">edit</span>Read and edit</a>
                                <form method="POST" action="{{ route('Blog.manage.approve', $post) }}" onsubmit="return confirm('Publish &quot;' + {{ json_encode($post->title) }} + '&quot; now?');">
                                    @csrf
                                    <button type="submit" class="dh-btn dh-btn-primary"><span class="material-icons-round" aria-hidden="true">publish</span>Publish</button>
                                </form>
                                <form method="POST" action="{{ route('Blog.manage.sendBack', $post) }}" onsubmit="var n = prompt('Note for the author (what to change):', ''); if (n === null) return false; this.querySelector('[name=note]').value = n; return true;">
                                    @csrf
                                    <input type="hidden" name="note" value="">
                                    <button type="submit" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">undo</span>Send back</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">{{ auth()->user()->isAdmin() ? 'All articles' : 'My articles' }}</h2>
                <a class="dh-btn dh-btn-primary" href="{{ route('Blog.manage.create') }}">
                    <span class="material-icons-round" aria-hidden="true">add</span>New article
                </a>
            </div>

            <div class="dh-admin-card">
                <div class="table-responsive">
                    <table class="dh-admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Category</th>
                                @if(auth()->user()->isAdmin())<th>Author</th>@endif
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                <tr>
                                    <td>
                                        <b>{{ $post->title }}</b>
                                        @if($post->status === 'published')
                                            <a href="{{ route('Blog.show', $post->slug) }}" class="dh-linkbtn" style="margin-left: 8px;" target="_blank" rel="noopener">View</a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="chip {{ $post->status === 'published' ? 'chip-yes' : 'chip-static' }}">{{ ['draft' => 'Draft', 'pending' => 'Waiting for review', 'published' => 'Published'][$post->status] ?? ucfirst($post->status) }}</span>
                                        @if ($post->status === 'draft' && $post->review_note)<span class="chip chip-static" title="{{ $post->review_note }}">Changes requested</span>@endif
                                    </td>
                                    <td>{{ $post->category }}</td>
                                    @if(auth()->user()->isAdmin())<td>{{ $post->author->name ?? 'Unknown' }}</td>@endif
                                    <td>{{ $post->updated_at->format('M j, Y') }}</td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <a href="{{ route('Blog.manage.edit', $post) }}" title="Edit"><i class="material-icons" style="font-size:20px;">edit</i></a>
                                            <button type="button" class="is-danger" title="Remove" onclick="confirmDeletePost('{{ route('Blog.manage.destroy', $post) }}', '{{ addslashes($post->title) }}')"><i class="material-icons" style="font-size:20px;">delete</i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dh-admin-empty-row">
                                    <td colspan="6">No articles yet. <a href="{{ route('Blog.manage.create') }}">Write the first one</a>.</td>
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
        function confirmDeletePost(url, title) {
            document.getElementById('deletePostConfirmText').textContent = 'Remove "' + title + '"?';
            document.getElementById('deletePostForm').action = url;
            new bootstrap.Modal(document.getElementById('modal-delete-post')).show();
        }
    </script>
    @endpush
</x-page-template>
