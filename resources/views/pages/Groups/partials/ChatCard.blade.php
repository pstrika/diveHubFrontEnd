<section class="dh-panel">
    <h2 class="dh-panel-title">Group chat</h2>
    <div id="groupChatMessages" data-count="{{ $messages->count() }}" style="max-height: 400px; overflow-y: auto;">
        @include('pages.Groups.partials.messages')
    </div>

    <hr class="dh-panel-divider">

    <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
        @csrf
        <div class="mb-2">
            <textarea name="body" id="chatBodyInput" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group... (Enter to send, Shift+Enter for a new line)"></textarea>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center">
                <button type="button" class="dh-btn dh-btn-ghost-dark me-2" data-bs-toggle="modal" data-bs-target="#modalChatPhotos">
                    <span class="material-icons-round" aria-hidden="true">add_photo_alternate</span>
                </button>
                <span id="chatPhotoPreview" class="d-flex align-items-center flex-wrap gap-1"></span>
            </span>
            <button type="submit" class="dh-btn dh-btn-primary">Post</button>
        </div>
    </form>
</section>
