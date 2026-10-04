{{--
    "Did you really book this?" confirmation, rendered once by the page
    template so it works wherever a .dh-book-link renders (TripDetails,
    trip-card, Dashboard).

    Background: clicking a Book link sends the diver to the operator's own
    site in a new tab - we have no way to know whether they actually booked.
    The JS below stashes the clicked trip in sessionStorage, then asks when
    the diver comes back to this tab (Pablo, 2026-10-04).

    Members only - a guest has no calendar to mark booked.
--}}
@auth
@if(auth()->user()->isNotGuest())
<div class="modal fade" id="modal_book_confirm" tabindex="-1" role="dialog"
     aria-labelledby="modal_book_confirm_title" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content dh-sheet">
            <div class="dh-sheet-head">
                <span class="dh-dive-status is-open"><span class="material-icons-round" aria-hidden="true">help_outline</span>Quick check</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <h6 class="dh-sheet-title" id="modal_book_confirm_title">Did you book <span id="dh-book-confirm-name">that dive</span>?</h6>
            <p class="text-sm text-secondary" style="margin: 0 0 14px;">If you completed the booking with the operator, we'll mark it booked and add it to your calendar.</p>
            <div class="d-flex gap-2">
                <a id="dh-book-confirm-yes" class="dh-btn dh-btn-primary dh-btn-block" href="">Yes, I booked it</a>
                <button type="button" class="dh-btn dh-btn-ghost-dark dh-btn-block" data-bs-dismiss="modal">Not yet</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    (function () {
        var PENDING_KEY = 'dh_pending_booking';
        var STALE_MS = 6 * 60 * 60 * 1000; // 6h - long enough for a slow checkout, short enough to not surface days later

        document.addEventListener('click', function (e) {
            var link = e.target.closest('.dh-book-link');
            if (!link) return;
            try {
                sessionStorage.setItem(PENDING_KEY, JSON.stringify({
                    tripId: link.dataset.tripId,
                    tripName: link.dataset.tripName || '',
                    ts: Date.now(),
                }));
            } catch (err) { /* sessionStorage unavailable - just skip the follow-up */ }
        });

        function showBookConfirm(pending) {
            var modalEl = document.getElementById('modal_book_confirm');
            if (!modalEl || !window.bootstrap) return;
            document.getElementById('dh-book-confirm-name').textContent = pending.tripName || 'that dive';
            document.getElementById('dh-book-confirm-yes').href = '{{ url('ConfirmTripBooked') }}/' + encodeURIComponent(pending.tripId);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState !== 'visible') return;
            var raw;
            try { raw = sessionStorage.getItem(PENDING_KEY); } catch (err) { return; }
            if (!raw) return;
            try { sessionStorage.removeItem(PENDING_KEY); } catch (err) { /* ignore */ }
            var pending;
            try { pending = JSON.parse(raw); } catch (err) { return; }
            if (!pending || !pending.tripId || (Date.now() - pending.ts) > STALE_MS) return;
            // A modal already open (another prompt beat us to the tab-focus
            // event) - don't stack a second one on top.
            if (document.querySelector('.modal.show')) return;
            showBookConfirm(pending);
        });
    })();
</script>
@endpush
@endif
@endauth
