<div class="card p-0 position-relative mt-4 mx-0 z-index-2 mb-4">
    <div class="card-header p-0 mt-n4 mx-3">
        <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
            <h2 class="card-title text-white mx-4">Group Chat</h2>
        </div>
    </div>
    <div class="card-body">
        <div id="groupChatMessages" data-count="{{ $messages->count() }}" style="max-height: 400px; overflow-y: auto;">
            @include('pages.Groups.partials.messages')
        </div>

        <hr class="horizontal dark">

        <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
            @csrf
            <div class="mb-2">
                <textarea name="body" id="chatBodyInput" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group... (Enter to send, Shift+Enter for a new line)"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 me-2" data-bs-toggle="modal" data-bs-target="#modalChatPhotos">
                        <i class="material-icons text-sm align-middle">add_photo_alternate</i>
                    </button>
                    <span id="chatPhotoPreview" class="d-flex align-items-center flex-wrap gap-1"></span>
                </span>
                <button type="submit" class="btn bg-gradient-info mb-0">Post</button>
            </div>
        </form>
    </div>
</div>
