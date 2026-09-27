// Generic interactivity for the <x-dh-select> themed dropdown component
// (resources/views/components/dh-select.blade.php) - open/close the menu,
// pick an option, keep the hidden input + visible label in sync. Fires a
// real 'change' event on the hidden input so any page can react (e.g.
// revealing a Save button) without this file knowing about it.
(function () {
    function closeAllDhSelects() {
        document.querySelectorAll('.dh-select-menu').forEach(function (m) { m.hidden = true; });
    }

    document.querySelectorAll('.dh-select').forEach(function (wrap) {
        var btn = wrap.querySelector('.dh-select-btn');
        var menu = wrap.querySelector('.dh-select-menu');
        var hidden = wrap.querySelector('input[type="hidden"]');
        var valueSpan = wrap.querySelector('.dh-select-value');
        if (!btn || !menu || !hidden || !valueSpan) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (btn.disabled) return;
            var wasOpen = !menu.hidden;
            closeAllDhSelects();
            menu.hidden = wasOpen;
        });

        menu.querySelectorAll('li').forEach(function (li) {
            li.addEventListener('click', function () {
                hidden.value = li.dataset.value;
                valueSpan.textContent = li.textContent;
                menu.querySelectorAll('li').forEach(function (o) { o.classList.remove('is-selected'); });
                li.classList.add('is-selected');
                menu.hidden = true;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    document.addEventListener('click', closeAllDhSelects);
})();
