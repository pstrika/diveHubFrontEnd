// Generic interactivity for the <x-dh-select> themed dropdown component
// (resources/views/components/dh-select.blade.php) - open/close the menu,
// pick an option, keep the hidden input + visible label in sync. Fires a
// real 'change' event on the hidden input so any page can react (e.g.
// revealing a Save button) without this file knowing about it.
//
// z-index boost on open (Pablo, 2026-10-15, DiveDetails's stacked
// Dive Operator/Conditions/Other cards): .dh-select-menu's own z-index:20
// only wins within its OWN stacking context. A card using Bootstrap's
// .z-index-2 (position:relative + z-index:2 !important) creates a new
// stacking context, so an open menu inside one card can't paint above a
// LATER sibling card at the same z-index - the next card's own header
// intercepts clicks meant for the menu. edit-site.blade.php's own
// <x-dh-select> usage never hit this (its .dh-panel containers have no
// z-index of their own to compete), so this went unnoticed until a page
// stacked multiple z-indexed cards each holding one. Fix: temporarily
// bump the nearest .card/.dh-panel ancestor's z-index while its menu is
// open, restore on close - a card with no such ancestor is unaffected.
// setProperty(..., 'important') because .z-index-2's own !important
// otherwise beats a plain inline style outright, boost or not.
(function () {
    var boosted = [];

    function closeAllDhSelects() {
        document.querySelectorAll('.dh-select-menu').forEach(function (m) { m.hidden = true; });
        boosted.forEach(function (el) {
            if (el.dataset.dhSelectZRestore === '') el.style.removeProperty('z-index');
            else el.style.zIndex = el.dataset.dhSelectZRestore;
            delete el.dataset.dhSelectZRestore;
        });
        boosted = [];
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
            if (!wasOpen) {
                var ancestor = wrap.closest('.card, .dh-panel');
                if (ancestor) {
                    ancestor.dataset.dhSelectZRestore = ancestor.style.zIndex || '';
                    ancestor.style.setProperty('z-index', '50', 'important');
                    boosted.push(ancestor);
                }
            }
        });

        menu.querySelectorAll('li[data-value]').forEach(function (li) {
            li.addEventListener('click', function () {
                hidden.value = li.dataset.value;
                valueSpan.textContent = li.textContent;
                menu.querySelectorAll('li[data-value]').forEach(function (o) { o.classList.remove('is-selected'); });
                li.classList.add('is-selected');
                menu.hidden = true;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    });

    document.addEventListener('click', closeAllDhSelects);
})();
