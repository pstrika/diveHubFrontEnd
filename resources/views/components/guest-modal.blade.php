{{--
    "Logged in as a guest" prompt, rendered once by the page template.

    Background: anonymous visitors are logged in as a shared guest user (see
    App\Http\Middleware\AuthenticateAsGuest). Locked sidebar items call
    showModalGuest() to explain why and offer an account. Before this
    component the modal markup was copy pasted into about eighteen page
    views, and the pages that lacked it (Home, Weather, the calendars,
    Groups) made every locked link a dead click. Rendering it once from
    page-template.blade.php fixes that everywhere at once. It sits at the
    body level, outside the sidebar, because Bootstrap modals must not be
    inside an element that gets a CSS transform (the sidenav does on phones).

    The "Create an account" link goes to the create-account route, which
    logs the guest user out and lands on sign up in one hop. A plain link to
    sign up would bounce, because the guest is technically logged in.
--}}
@php
    // Anonymous (no session yet, e.g. a first hit on "/") and the shared guest
    // user both count as guests here. Only real accounts skip the prompt.
    $__u = auth()->user();
    $__isGuest = !$__u || !$__u->isNotGuest();
@endphp
@if($__isGuest)
<div class="modal fade" id="modal_logged_as_guest" tabindex="-1" role="dialog"
     aria-labelledby="modal_logged_as_guest_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h6 class="modal-title font-weight-normal" id="modal_logged_as_guest_title">You are browsing as a guest</h6>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="py-3 text-center">
                    <i class="material-icons-round h1 text-primary" aria-hidden="true">lock</i>
                    <h4 class="text-gradient text-info text-md mt-4">Create a free account to save trips, plan dives and see the forecast. No credit card, ever.</h4>
                    <a class="btn bg-gradient-info mt-3" href="{{ route('create-account') }}">Create an account</a>
                    <p class="text-sm text-secondary mt-3 mb-0">Or keep browsing. Press anywhere outside this box to continue.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Called by the locked sidebar links. Defined here, beside the modal,
    // so both always ship together. Uses the Bootstrap 5 API directly
    // because jQuery is only loaded on some pages, and the old jQuery call
    // would throw on the pages that never had the modal before.
    function showModalGuest() {
        var el = document.getElementById('modal_logged_as_guest');
        if (el && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }
</script>
@endpush
@endif
