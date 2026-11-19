{{--
    Shop portal: buttons that open a form under them. A button names its
    target with data-shop-toggle="id". Opening one closes the others in the
    same card, so only one thing is being edited at a time.
--}}
@once
@push('js')
<script>
    document.querySelectorAll('[data-shop-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.getAttribute('data-shop-toggle'));
            if (!target) return;
            var open = target.hidden;
            var card = btn.closest('.dh-shop-card, .dh-panel') || document;
            card.querySelectorAll('.dh-shop-drawer, .dh-shop-editrow').forEach(function (d) { d.hidden = true; });
            card.querySelectorAll('[data-shop-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
            target.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                var first = target.querySelector('input, select, textarea');
                if (first) first.focus();
                target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    });
</script>
@endpush
@endonce
