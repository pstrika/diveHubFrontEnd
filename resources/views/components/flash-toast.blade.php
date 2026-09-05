@if(session('msg'))
    <div id="flashToast" class="alert alert-success alert-dismissible fade show position-fixed text-sm py-2 px-3"
        style="top: 90px; right: 24px; z-index: 1080; max-width: 320px; box-shadow: 0 4px 12px rgba(0,0,0,.15);" role="alert">
        {{ session('msg') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        setTimeout(function () {
            var el = document.getElementById('flashToast');
            if (el && window.bootstrap) {
                bootstrap.Alert.getOrCreateInstance(el).close();
            } else if (el) {
                el.remove();
            }
        }, 4000);
    </script>
@endif
