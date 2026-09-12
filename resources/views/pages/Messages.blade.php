<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    {{-- Message detail --}}
    <div class="modal fade" id="modal_message" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal d-flex align-items-center gap-2" id="modal-title">
                        <span class="material-icons-round" aria-hidden="true">drafts</span><span id="modal-title-text"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-secondary mb-1" id="modal-subtitle-1"></p>
                    <p class="text-xs text-secondary mb-2" id="modal-subtitle-2"></p>
                    <p class="mb-0" id="modal-body"></p>
                </div>
            </div>
        </div>
    </div>

    <main class="main-content position-relative h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="Notifications" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                @if($messages->isEmpty())
                    <div class="dh-empty">
                        <span class="material-icons-round" aria-hidden="true">notifications_none</span>
                        <p>No notifications yet.</p>
                    </div>
                @else
                    <div class="dh-msg-list">
                        @foreach($messages as $message)
                            <div class="dh-msg-row {{ $message->read ? '' : 'is-unread' }}" id="message-row-{{ $message->id }}">
                                <span class="material-icons-round dh-msg-icon" id="icon-read-{{ $message->id }}">{{ $message->read ? 'drafts' : 'mail' }}</span>
                                <a href="javascript:void(0)" class="dh-msg-main" onclick="showMessage({{ $message->id }})">
                                    <span class="dh-msg-subject" id="subject-inner-{{ $message->id }}">{{ $message->subject }}</span>
                                    <span class="dh-msg-meta">{{ $message->fromUser->name ?? 'Divers Hub' }} &middot; {{ $message->created_at->format('M j, Y g:ia') }}</span>
                                </a>
                                <button type="button" class="dh-icon-btn" aria-label="Delete notification" onclick="deleteMessage({{ $message->id }})">
                                    <span class="material-icons-round">delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    <script>
        var messages = @json($messages);

        function formatDate(dateString) {
            var date = new Date(dateString);
            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            var hours = ('0' + date.getHours()).slice(-2);
            var minutes = ('0' + date.getMinutes()).slice(-2);
            return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;
        }

        function deleteMessage(messageId) {
            if (!confirm('Are you sure you want to delete this message?')) {
                return;
            }
            $.ajax({
                url: '/delete-message',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', noteId: messageId },
                success: function() {
                    var row = document.getElementById('message-row-' + messageId);
                    if (row) { row.remove(); }
                },
                error: function() {
                    alert('An error occurred while deleting the message.');
                }
            });
        }

        function showMessage(messageId) {
            var message = messages.find(m => m.id === messageId);
            if (!message) {
                return;
            }
            if (!message.read) {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: '/mark-as-read',
                    type: 'POST',
                    data: { _token: CSRF_TOKEN, noteId: messageId }
                });
                var row = document.getElementById('message-row-' + messageId);
                if (row) { row.classList.remove('is-unread'); }
                var icon = document.getElementById('icon-read-' + messageId);
                if (icon) { icon.textContent = 'drafts'; }
            }

            document.getElementById('modal-title-text').textContent = message.subject;
            document.getElementById('modal-subtitle-1').textContent = 'From: ' + (message.from_user ? message.from_user.name : 'Divers Hub');
            document.getElementById('modal-subtitle-2').textContent = 'on: ' + formatDate(message.created_at);
            document.getElementById('modal-body').textContent = message.body;

            new bootstrap.Modal(document.getElementById('modal_message')).show();
        }
    </script>
    @endpush
</x-page-template>
