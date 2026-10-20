/**
 * DiveDetails page interactivity beyond the chart (Pablo, 2026-10-15):
 * per-card edit-to-unlock, classification/site quick-edit chips, the
 * operator/boat/site search pickers (client-side filter over a small
 * server-provided catalog, same pattern edit-site.blade.php's own
 * operator search uses), the buddy @-mention search (a real endpoint
 * instead - the user directory is too large to ship whole), the
 * visibility slider, tags, and the dive-type multi-select chips.
 *
 * Reads window.dhOperators ([{id,name,logoUrl}]), window.dhBoatsByOperator
 * ({operatorId: [{name,pic},...]}), window.dhSites ([{id,name,slug}]).
 */
(function () {
    'use strict';

    // z-index boost while a results list is open (Pablo, 2026-10-15: "when
    // selecting the operator, then the boat dropdown was bleeding into the
    // Dive Conditions card") - same root cause and fix as dh-select.js's
    // own menus: a card using Bootstrap's .z-index-2 (position:relative +
    // z-index:2 !important) creates its own stacking context, so a results
    // list open inside one card can't paint above a later sibling card at
    // the same z-index without its own ancestor's z-index being boosted
    // too (setProperty(..., 'important') since .z-index-2's own !important
    // otherwise beats a plain inline style outright).
    function boostAncestorZ(el) {
        var ancestor = el.closest('.card, .dh-panel');
        if (!ancestor || ancestor.dataset.dhSearchZRestore !== undefined) return;
        ancestor.dataset.dhSearchZRestore = ancestor.style.zIndex || '';
        ancestor.style.setProperty('z-index', '50', 'important');
    }
    function restoreAncestorZ(el) {
        var ancestor = el.closest('.card, .dh-panel');
        if (!ancestor || ancestor.dataset.dhSearchZRestore === undefined) return;
        if (ancestor.dataset.dhSearchZRestore === '') ancestor.style.removeProperty('z-index');
        else ancestor.style.zIndex = ancestor.dataset.dhSearchZRestore;
        delete ancestor.dataset.dhSearchZRestore;
    }

    // Generic "type to filter a small list, pick a result, free text still
    // allowed" widget - an <input> plus a following .dh-search-list div.
    function setupSearchPicker(opts) {
        var input = opts.input;
        var results = opts.results;
        if (!input || !results) return;

        input.addEventListener('input', function () {
            if (opts.onTypeFree) opts.onTypeFree();
            var q = input.value.trim().toLowerCase();
            if (!q) {
                results.hidden = true;
                restoreAncestorZ(input);
                return;
            }
            var matches = opts.items.filter(function (item) {
                return opts.getLabel(item).toLowerCase().indexOf(q) !== -1;
            }).slice(0, 10);
            results.innerHTML = '';
            if (!matches.length) {
                results.hidden = true;
                restoreAncestorZ(input);
                return;
            }
            matches.forEach(function (item) {
                var a = document.createElement('a');
                a.href = '#';
                a.textContent = opts.getLabel(item);
                if (opts.decorate) opts.decorate(item, a);
                a.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    input.value = opts.getLabel(item);
                    results.hidden = true;
                    restoreAncestorZ(input);
                    opts.onPick(item);
                });
                results.appendChild(a);
            });
            results.hidden = false;
            boostAncestorZ(input);
        });
        input.addEventListener('blur', function () {
            setTimeout(function () { results.hidden = true; restoreAncestorZ(input); }, 150);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Collapsible cards (Pablo, 2026-10-15: "make Dive Setup, Dive
        // Operator, Dive Conditions, and Other cards collapsable") - same
        // setOpen()/chevron pattern as DecoPlanner's own "Inputs" card
        // (DivePlanner.blade.php's #dh-deco-inputs-toggle / deco-planner.js).
        // The per-card pencil button lives inside this same clickable
        // header, so it stops propagation - otherwise opening the edit
        // form would also collapse the card out from under it.
        document.querySelectorAll('[data-collapsible-toggle]').forEach(function (head) {
            var body = document.getElementById(head.getAttribute('aria-controls'));
            var chevron = head.querySelector('.dh-calc-inputs-chevron');
            if (!body) return;
            function setOpen(open) {
                body.hidden = !open;
                head.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (chevron) chevron.textContent = open ? 'expand_less' : 'expand_more';
                // A responsive Chart.js canvas built while its card was
                // collapsed measured a 0x0 container - size it now that
                // the container has a box (the Gases card loads collapsed).
                if (open && window.Chart && Chart.getChart) {
                    body.querySelectorAll('canvas').forEach(function (c) {
                        var chart = Chart.getChart(c);
                        if (chart) chart.resize();
                    });
                }
            }
            head.addEventListener('click', function () { setOpen(body.hidden); });
            head.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen(body.hidden); }
            });
            var editBtn = head.querySelector('.dh-dive-card-edit-btn');
            if (editBtn) editBtn.addEventListener('click', function (e) { e.stopPropagation(); });
        });

        // Per-card edit-to-unlock (Pablo, 2026-10-15: "add a pencil edit
        // button per card that will enable the edition of all data in
        // that card...go back to spans or labels (no inputs)"). Each card
        // holds one [data-view] block and one [data-edit] <form>; the
        // pencil swaps which is visible, Cancel swaps back without saving.
        document.querySelectorAll('[data-edit-card]').forEach(function (btn) {
            var card = document.getElementById(btn.dataset.editCard);
            if (!card) return;
            var view = card.querySelector('[data-view]');
            var edit = card.querySelector('[data-edit]');
            if (!view || !edit) return;
            btn.addEventListener('click', function () {
                view.hidden = true;
                edit.hidden = false;
                // The pencil lives in the same collapsible header as the
                // card body it's about to reveal fields inside of - force
                // the card open too, or the freshly-unhidden form stays
                // invisible under a still-collapsed body.
                var head = card.querySelector('[data-collapsible-toggle]');
                if (head) {
                    var body = document.getElementById(head.getAttribute('aria-controls'));
                    if (body && body.hidden) {
                        body.hidden = false;
                        head.setAttribute('aria-expanded', 'true');
                        var chev = head.querySelector('.dh-calc-inputs-chevron');
                        if (chev) chev.textContent = 'expand_less';
                    }
                }
                var firstField = edit.querySelector('input, textarea, .dh-select-btn');
                if (firstField) firstField.focus();
            });
            var cancelBtn = edit.querySelector('[data-cancel-edit]');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    edit.hidden = true;
                    view.hidden = false;
                });
            }
        });

        // Classification quick-edit - reveal the small form next to the
        // merged OC/CC + classification chip; the chip itself stays
        // visible (it still shows the immutable OC/CC half).
        var classBtn = document.getElementById('diveClassEditBtn');
        var classForm = document.getElementById('diveClassForm');
        if (classBtn && classForm) {
            classBtn.addEventListener('click', function () {
                classForm.hidden = false;
                classForm.querySelector('select').focus();
            });
        }

        // Site quick-edit, same reveal pattern.
        var siteBtn = document.getElementById('diveSiteEditBtn');
        var siteForm = document.getElementById('diveSiteForm');
        if (siteBtn && siteForm) {
            siteBtn.addEventListener('click', function () {
                siteForm.hidden = false;
                siteForm.querySelector('input').focus();
            });
        }

        // Site search - picking a real site fills the hidden site_id (the
        // catalog match); typing free text clears it, so the save goes
        // through conditions.site_name_custom instead (see
        // LogbookController::updateConditions).
        // Items carry custom:true for the diver's own sites (user_sites) -
        // those fill user_site_id instead of site_id; the two are exclusive.
        var siteIdInput = document.getElementById('diveSiteIdInput');
        var userSiteIdInput = document.getElementById('diveUserSiteIdInput');
        setupSearchPicker({
            input: document.getElementById('diveSiteSearch'),
            results: document.getElementById('diveSiteResults'),
            items: window.dhSites || [],
            getLabel: function (s) { return s.name; },
            decorate: function (s, a) {
                if (s.custom) {
                    var tag = document.createElement('span');
                    tag.className = 'dh-search-item-own';
                    tag.textContent = 'yours';
                    a.appendChild(tag);
                }
            },
            onPick: function (s) {
                if (siteIdInput) siteIdInput.value = s.custom ? '' : s.id;
                if (userSiteIdInput) userSiteIdInput.value = s.custom ? s.id : '';
            },
            onTypeFree: function () {
                if (siteIdInput) siteIdInput.value = '';
                if (userSiteIdInput) userSiteIdInput.value = '';
            },
        });

        // Operator search - picking a real operator shows its logo
        // (Pablo, 2026-10-15: "a small logo of the operator next to the
        // input"), stores the logo URL in a hidden field so it survives
        // the save, and unlocks that operator's boat names (each with its
        // own small picture) as quick picks below. Free text clears both
        // the logo and the boat suggestions - nothing to show without a
        // real catalog match.
        var operatorByName = {};
        (window.dhOperators || []).forEach(function (o) { operatorByName[o.name] = o; });
        var operatorInput = document.getElementById('diveOperatorSearch');
        var operatorLogoInput = document.getElementById('diveOperatorLogoInput');
        var operatorUrlInput = document.getElementById('diveOperatorUrlInput');
        var selectedOperatorId = operatorInput ? (operatorByName[operatorInput.value] || {}).id : null;

        // One boat-picture frame (logo overlaid) is shared by view and edit
        // mode (Pablo, 2026-10-15: "do not show the small logo badge inline
        // with operator and boat name") - a pick just repoints it, from
        // the same hidden fields the save reads.
        var frameCol = document.getElementById('diveBoatFrameCol');
        var frameImg = document.getElementById('diveBoatFrameImg');
        var frameLogo = document.getElementById('diveOperatorFrameLogo');
        // Same three states the blade renders on load (Pablo, 2026-10-15):
        // operator + boat known -> half-width inputs beside the picture;
        // operator known, boat not -> full-width inputs, inline logo only;
        // operator not known -> full-width, nothing. A catalog pick is the
        // only way a logo/pic gets into the hidden fields, so "known"
        // just means the field is non-empty.
        function updateBoatFrame() {
            if (!frameCol) return;
            var pic = boatPicInput ? boatPicInput.value : '';
            var logo = operatorLogoInput ? operatorLogoInput.value : '';
            var showFrame = !!(logo && pic);
            if (frameImg && pic) frameImg.src = pic;
            if (frameLogo && logo) frameLogo.src = logo;
            frameCol.hidden = !showFrame;
            document.querySelectorAll('#diveOperatorBody [data-op-col]').forEach(function (col) {
                col.classList.toggle('col-md-6', showFrame);
            });
            document.querySelectorAll('#diveOperatorBody [data-op-inline-logo]').forEach(function (img) {
                var show = !!logo && !showFrame;
                img.hidden = !show;
                if (show) img.src = logo;
            });
        }

        var boatInput = document.getElementById('diveBoatName');
        var boatPicInput = document.getElementById('diveBoatPicInput');
        var boatResults = document.getElementById('diveBoatResults');

        // Cancel discards unsaved picks and re-syncs the frame, otherwise
        // view mode keeps showing the abandoned pick. form.reset() alone
        // can't do it: a hidden input's .value IS its default, so a pick
        // overwrote the saved value. Snapshot the three hidden fields here
        // (a Save reloads the page, so this is always the saved state) and
        // put them back by hand.
        var operatorForm = document.querySelector('#diveOperatorBody form[data-edit]');
        var operatorCancel = operatorForm && operatorForm.querySelector('[data-cancel-edit]');
        var savedOperatorFields = [operatorLogoInput, operatorUrlInput, boatPicInput].map(function (el) { return el ? el.value : ''; });
        if (operatorCancel) {
            operatorCancel.addEventListener('click', function () {
                operatorForm.reset();
                [operatorLogoInput, operatorUrlInput, boatPicInput].forEach(function (el, i) { if (el) el.value = savedOperatorFields[i]; });
                selectedOperatorId = operatorInput ? (operatorByName[operatorInput.value] || {}).id : null;
                updateBoatFrame();
            });
        }
        function renderBoatSuggestions() {
            if (!boatResults) return;
            var boats = (selectedOperatorId && window.dhBoatsByOperator) ? (window.dhBoatsByOperator[selectedOperatorId] || []) : [];
            boatResults.innerHTML = '';
            if (!boats.length) {
                boatResults.hidden = true;
                restoreAncestorZ(boatInput);
                return;
            }
            boats.forEach(function (boat) {
                var a = document.createElement('a');
                a.href = '#';
                a.textContent = boat.name;
                a.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    boatInput.value = boat.name;
                    if (boatPicInput) boatPicInput.value = boat.pic || '';
                    updateBoatFrame();
                    boatResults.hidden = true;
                    restoreAncestorZ(boatInput);
                });
                boatResults.appendChild(a);
            });
            boatResults.hidden = false;
            boostAncestorZ(boatInput);
        }

        setupSearchPicker({
            input: operatorInput,
            results: document.getElementById('diveOperatorResults'),
            items: window.dhOperators || [],
            getLabel: function (o) { return o.name; },
            onPick: function (o) {
                selectedOperatorId = o.id;
                if (operatorLogoInput) operatorLogoInput.value = o.logoUrl || '';
                if (operatorUrlInput) operatorUrlInput.value = o.url || '';
                updateBoatFrame();
                renderBoatSuggestions();
            },
            onTypeFree: function () {
                selectedOperatorId = null;
                if (operatorLogoInput) operatorLogoInput.value = '';
                if (operatorUrlInput) operatorUrlInput.value = '';
                updateBoatFrame();
            },
        });
        if (boatInput && boatResults) {
            boatInput.addEventListener('focus', renderBoatSuggestions);
            boatInput.addEventListener('blur', function () {
                setTimeout(function () { boatResults.hidden = true; restoreAncestorZ(boatInput); }, 150);
            });
            boatInput.addEventListener('input', function () {
                if (boatPicInput) boatPicInput.value = '';
                updateBoatFrame();
            });
        }

        // Buddy @-mention search - a real endpoint (the user directory is
        // personal data at real scale, unlike the small catalogs above).
        // Debounced, triggers on the text after the LAST "@" in the field
        // (so "Zach, @Mar" searches "Mar" without disturbing "Zach,").
        var buddyInput = document.getElementById('diveBuddies');
        var buddyResults = document.getElementById('diveBuddyResults');
        var buddySearchTimer = null;
        if (buddyInput && buddyResults) {
            buddyInput.addEventListener('input', function () {
                var val = buddyInput.value;
                var atIndex = val.lastIndexOf('@');
                var query = atIndex === -1 ? '' : val.slice(atIndex + 1);
                if (atIndex === -1 || query.length < 2 || query.indexOf(',') !== -1) {
                    buddyResults.hidden = true;
                    restoreAncestorZ(buddyInput);
                    return;
                }
                clearTimeout(buddySearchTimer);
                buddySearchTimer = setTimeout(function () {
                    fetch(window.dhBuddySearchUrl + '?q=' + encodeURIComponent(query))
                        .then(function (r) { return r.json(); })
                        .then(function (users) {
                            buddyResults.innerHTML = '';
                            if (!users.length) {
                                buddyResults.hidden = true;
                                restoreAncestorZ(buddyInput);
                                return;
                            }
                            users.forEach(function (u) {
                                var a = document.createElement('a');
                                a.href = '#';
                                a.textContent = u.name;
                                a.addEventListener('mousedown', function (e) {
                                    e.preventDefault();
                                    buddyInput.value = val.slice(0, atIndex) + u.name + ', ';
                                    buddyResults.hidden = true;
                                    restoreAncestorZ(buddyInput);
                                    buddyInput.focus();
                                });
                                buddyResults.appendChild(a);
                            });
                            buddyResults.hidden = false;
                            boostAncestorZ(buddyInput);
                        })
                        .catch(function () { buddyResults.hidden = true; restoreAncestorZ(buddyInput); });
                }, 200);
            });
            buddyInput.addEventListener('blur', function () {
                setTimeout(function () { buddyResults.hidden = true; restoreAncestorZ(buddyInput); }, 150);
            });
        }

        // Multi-select dive-type chips toggle their own hidden checkbox
        // and visual state on click - plain checkboxes under the hood
        // so the existing form-submit path needs no JS of its own.
        document.querySelectorAll('.dh-dive-type-chip').forEach(function (label) {
            var input = label.querySelector('input');
            label.addEventListener('click', function (e) {
                e.preventDefault();
                input.checked = !input.checked;
                label.classList.toggle('is-active', input.checked);
            });
        });

        // Tags (Pablo, 2026-10-15: "a line for tags, up to 5, free text").
        // Own small form, saves immediately on add/remove - not gated
        // behind a pencil like the bigger cards, low-stakes quick-access
        // data (same reasoning dive-type chips already don't need one).
        var tagInput = document.getElementById('diveTagInput');
        var tagChips = document.getElementById('diveTagChips');
        var tagsForm = document.getElementById('diveTagsForm');
        if (tagInput && tagChips && tagsForm) {
            function addTagChip(tag) {
                var chip = document.createElement('span');
                chip.className = 'dh-dive-tag-chip';
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'tags[]';
                hidden.value = tag;
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.setAttribute('aria-label', 'Remove tag');
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function () {
                    chip.remove();
                    tagsForm.submit();
                });
                chip.appendChild(document.createTextNode(tag + ' '));
                chip.appendChild(hidden);
                chip.appendChild(removeBtn);
                tagChips.appendChild(chip);
            }
            // Suggest tags the diver has used before (Pablo, 2026-10-20) -
            // most used first, filtered as you type, arrows/Enter/click pick.
            var suggestBox = document.getElementById('diveTagSuggest');
            var allTags = window.dhDiveTagSuggestions || [];
            var suggestActive = -1;
            function currentTags() {
                return Array.prototype.map.call(tagChips.querySelectorAll('input[name="tags[]"]'), function (i) { return i.value.toLowerCase(); });
            }
            function suggestions() {
                var q = tagInput.value.trim().toLowerCase(), have = currentTags();
                return allTags.filter(function (t) {
                    var l = t.toLowerCase();
                    return have.indexOf(l) === -1 && (!q || l.indexOf(q) !== -1);
                }).sort(function (a, b) {
                    // Prefix matches first, then the most-used order they came in.
                    var pa = q && a.toLowerCase().indexOf(q) === 0 ? 0 : 1, pb = q && b.toLowerCase().indexOf(q) === 0 ? 0 : 1;
                    return pa - pb;
                }).slice(0, 8);
            }
            function hideSuggest() { if (suggestBox) { suggestBox.hidden = true; suggestBox.innerHTML = ''; } suggestActive = -1; }
            function showSuggest() {
                if (!suggestBox || tagChips.children.length >= 5) return;
                var list = suggestions();
                if (!list.length) { hideSuggest(); return; }
                suggestBox.innerHTML = '';
                list.forEach(function (t, i) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'dh-mention-item' + (i === suggestActive ? ' is-active' : '');
                    b.textContent = t;
                    b.addEventListener('mousedown', function (e) { e.preventDefault(); pickTag(t); });
                    suggestBox.appendChild(b);
                });
                suggestBox.hidden = false;
            }
            function pickTag(value) {
                if (tagChips.children.length >= 5) { tagInput.placeholder = 'Up to 5 tags only'; return; }
                addTagChip(value);
                tagInput.value = '';
                hideSuggest();
                tagsForm.submit();
            }
            tagInput.addEventListener('focus', showSuggest);
            tagInput.addEventListener('input', function () { suggestActive = -1; showSuggest(); });
            tagInput.addEventListener('blur', function () { setTimeout(hideSuggest, 150); });
            tagInput.addEventListener('keydown', function (e) {
                var items = suggestBox && !suggestBox.hidden ? suggestBox.querySelectorAll('.dh-mention-item') : [];
                if (e.key === 'ArrowDown' && items.length) { e.preventDefault(); suggestActive = (suggestActive + 1) % items.length; showSuggest(); return; }
                if (e.key === 'ArrowUp' && items.length) { e.preventDefault(); suggestActive = (suggestActive - 1 + items.length) % items.length; showSuggest(); return; }
                if (e.key === 'Escape') { hideSuggest(); return; }
                if (e.key !== 'Enter') return;
                e.preventDefault();
                if (items.length && suggestActive >= 0) { pickTag(items[suggestActive].textContent); return; }
                var value = tagInput.value.trim();
                if (!value) return;
                if (tagChips.children.length >= 5) {
                    tagInput.placeholder = 'Up to 5 tags only';
                    return;
                }
                addTagChip(value);
                tagInput.value = '';
                tagsForm.submit();
            });
        }

        // Visibility slider + text input (Pablo, 2026-10-15: "a slider for
        // visibility and a text input, same we use in the deco planner...
        // make sure we use metric or imperial depending on what's set.
        // Step in the slider is by 10s from 0 to 100 ft, equivalent to 0
        // to 30m in steps of 3m"). Stored in meters regardless of what's
        // displayed (diveVisibilityMeters is the single source of truth);
        // the slider's own range/step is rebuilt on every unit toggle,
        // not just its displayed number reformatted, since the spec is a
        // genuinely different range per unit, not just a relabeled one.
        var visSlider = document.getElementById('diveVisibilitySlider');
        var visInput = document.getElementById('diveVisibilityInput');
        var visUnitLabel = document.getElementById('diveVisibilityUnit');
        var visMetersInput = document.getElementById('diveVisibilityMeters');
        if (visSlider && visInput && visMetersInput && typeof noUiSlider !== 'undefined') {
            function visRangeFor(unit) {
                return unit === 'imperial'
                    ? { min: 0, max: 100, step: 10, unitLabel: 'ft', toDisplay: window.dhUnits.mToFt, toMeters: function (ft) { return ft / 3.28084; } }
                    : { min: 0, max: 30, step: 3, unitLabel: 'm', toDisplay: function (m) { return m; }, toMeters: function (m) { return m; } };
            }

            var currentMeters = parseFloat(visMetersInput.value) || 0;
            var cfg = visRangeFor(window.dhUnits.getUnit());
            // The slider snaps the shown number to its step (nearest 10 ft /
            // 3 m - Pablo, 2026-10-15), but a programmatic rebuild (page load,
            // unit toggle) must not write that snapped value back into the
            // stored meters, or a saved 18 m would drift to 18.3 just from
            // viewing it in feet. Only the diver's own drag/typing stores.
            var visRebuilding = true;
            noUiSlider.create(visSlider, {
                start: cfg.toDisplay(currentMeters),
                connect: [true, false],
                range: { min: cfg.min, max: cfg.max },
                step: cfg.step,
            });
            visRebuilding = false;
            visUnitLabel.textContent = cfg.unitLabel;

            visSlider.noUiSlider.on('update', function (values) {
                var displayVal = Math.round(parseFloat(values[0]));
                visInput.value = displayVal;
                if (!visRebuilding) visMetersInput.value = Math.round(cfg.toMeters(displayVal) * 10) / 10;
            });
            visInput.addEventListener('change', function () {
                var v = parseFloat(visInput.value);
                if (isNaN(v)) return;
                visSlider.noUiSlider.set(Math.max(cfg.min, Math.min(cfg.max, v)));
            });

            document.addEventListener('dh-units-changed', function (e) {
                cfg = visRangeFor(e.detail.unit);
                var metersNow = parseFloat(visMetersInput.value) || 0;
                visRebuilding = true;
                visSlider.noUiSlider.updateOptions({
                    range: { min: cfg.min, max: cfg.max },
                    step: cfg.step,
                    start: cfg.toDisplay(metersNow),
                }, true);
                visRebuilding = false;
                visUnitLabel.textContent = cfg.unitLabel;
            });
        }

        // Gases used (Pablo, 2026-10-15: "I want the gases in each row,
        // not the tank. Then we can associate the tanks to the gases";
        // follow-up: "use a pencil Icon to edit the whole Gases used
        // section...show button save"). One edit-to-unlock for the WHOLE
        // section (not per row) - picking a tank with AI, its cylinder
        // size, the bottom/deco/travel role, and "not used this dive" all
        // live in one form, submitted once via Save. A real page reload
        // is what applies the result (chart RMV series, tank_config,
        // etc. all re-render fresh from the saved conditions) - nothing
        // here tries to fake that up live, unlike the old per-row
        // fetch-and-redraw version.
        var gasRows = document.querySelectorAll('.dh-dive-gas-row');
        if (gasRows.length && window.dhTankWaterVolumeLiters && window.dhTankPressureAt) {
            // First and last REAL pressure reading for one tank across
            // the whole dive (Pablo, 2026-10-15: start/end pressure pills
            // "you get the[m] from the first and last read of that
            // tank") - the shared basis for avgSacBar() below, the
            // start/end pressure pills, and the tank-fill graphic's own
            // percentage, so there's one reading of "first/last", not a
            // second copy that could drift from the first.
            function tankPressureRange(tankIndex) {
                var profile = window.dhDiveProfile || [];
                var first = null, last = null, depthSum = 0, depthCount = 0;
                for (var i = 0; i < profile.length; i++) {
                    var p = window.dhTankPressureAt(profile[i], tankIndex);
                    if (p !== null) {
                        if (!first) first = { time: profile[i].time, p: p };
                        last = { time: profile[i].time, p: p };
                    }
                    if (profile[i].depth !== null && profile[i].depth !== undefined) {
                        depthSum += profile[i].depth;
                        depthCount++;
                    }
                }
                if (!first || !last) return null;
                return { first: first.p, last: last.p, dtMin: (last.time - first.time) / 60, avgDepth: depthCount ? depthSum / depthCount : 0 };
            }

            // Average SAC (bar/min, surface-equivalent) across the whole
            // dive for one tank - same shape a dive computer's own
            // "average SAC" stat uses, not a point-by-point average of
            // the chart's rolling-window series (a second, coarser
            // calculation, not a re-derivation of the first).
            function avgSacBar(tankIndex) {
                var range = tankPressureRange(tankIndex);
                if (!range || range.dtMin <= 0) return null;
                var ata = 1 + range.avgDepth / 10;
                var deltaP = Math.max(0, range.first - range.last);
                return (deltaP / range.dtMin) / ata;
            }

            // "Nominal size" is NOT a straight unit conversion of the
            // water volume - a diver's "AL-80" means 77.5 cuft of FREE
            // GAS at 3000psi, not "0.4 cuft of water capacity" (naively
            // running the physical water-liters figure through the
            // liters-per-cuft factor). Same convention DecoPlanner's own
            // dhTankVolumeLabel() uses: imperial shows the catalog's raw
            // cuft rating, metric shows the actual water liters.
            function formatVolume(type, twins) {
                var t = window.DH_TANK_TYPES[type];
                if (!t) return '';
                var unit = window.dhUnits.getUnit();
                if (unit === 'imperial') return (t.cuft * (twins ? 2 : 1)).toFixed(1) + ' cuft';
                return window.dhTankWaterVolumeLiters(type, twins).toFixed(1) + ' L';
            }
            function formatRmv(litersPerMin) {
                var unit = window.dhUnits.getUnit();
                return unit === 'imperial'
                    ? window.dhTankLitersToCuft(litersPerMin).toFixed(2) + ' cuft/min'
                    : litersPerMin.toFixed(2) + ' L/min';
            }
            function formatPressure(bar) {
                var unit = window.dhUnits.getUnit();
                return unit === 'imperial'
                    ? Math.round(window.dhUnits.barToPsi(bar)) + ' psi'
                    : Math.round(bar) + ' bar';
            }

            // View-mode volume/RMV/pressure pills + the tank-fill graphic,
            // computed once from whatever's already saved
            // (conditions.tank_config, read server-side into each row's
            // edit fields) - edits only take effect after Save reloads
            // the page, so there's nothing to keep live here.
            gasRows.forEach(function (row, idx) {
                var sizeWrap = row.querySelector('[data-tank-size-wrap]');
                var tankIndex = sizeWrap ? sizeWrap.dataset.tankIndex : '';
                var typeInput = sizeWrap ? sizeWrap.querySelector('.dh-select input[type="hidden"]') : null;
                var type = typeInput ? typeInput.value : '';
                var twinsInput = sizeWrap ? sizeWrap.querySelector('[data-tank-twins-input]') : null;
                var twins = !!(twinsInput && twinsInput.checked);

                // Gas-content bar behind the tank template (Pablo,
                // 2026-10-15: "they should show the tank content like we
                // do in best gases or deco planner") - the same O2/He/N2
                // stacked bar, same colors, same image-over-canvas
                // technique as GasPlanning.blade.php's stackedBarChart,
                // drawn from the row's own mix - so it needs no AI
                // pressure data or tank size, just the gas. Twins get one
                // bar per cylinder. No % labels: at this size they'd be
                // unreadable, and the pill next to it already says it.
                var canvasEl = document.getElementById('tankMiniChart' + idx);
                if (canvasEl && window.Chart) {
                    var o2 = Math.max(0, Math.min(100, parseFloat(canvasEl.dataset.o2) || 0));
                    var he = Math.max(0, Math.min(100 - o2, parseFloat(canvasEl.dataset.he) || 0));
                    var n2 = 100 - o2 - he;
                    var bars = canvasEl.dataset.twins === '1' ? 2 : 1;
                    var fill = function (v) { var a = []; for (var b = 0; b < bars; b++) a.push(v); return a; };
                    var seg = function (v, color) { return { data: fill(v), backgroundColor: color, borderRadius: 0, barPercentage: bars === 2 ? 0.84 : 1, categoryPercentage: 1 }; };
                    new Chart(canvasEl.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: fill(''),
                            datasets: [seg(o2, '#2e7d4f'), seg(he, '#0e7c9e'), seg(n2, '#5a6b78')],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            plugins: { legend: { display: false }, tooltip: { enabled: false } },
                            scales: {
                                x: { stacked: true, display: false },
                                y: { stacked: true, display: false, min: 0, max: 100 },
                            },
                        },
                    });
                }

                if (!tankIndex) return;
                // One row can carry two cylinders (a sidemount pair) - each
                // gets its own start/end pills; volume and RMV add up.
                var tankIndices = (sizeWrap.dataset.tankIndices || String(tankIndex)).split(',').filter(Boolean);
                var volumeEl = document.getElementById('tankVolumeView' + idx);
                var rmvEl = document.getElementById('tankAvgRmvView' + idx);
                tankIndices.forEach(function (ti) {
                    var range = tankPressureRange(ti);
                    var startEl = document.getElementById('tankStartPressure' + idx + '_' + ti);
                    var endEl = document.getElementById('tankEndPressure' + idx + '_' + ti);
                    if (range) {
                        if (startEl) startEl.textContent = formatPressure(range.first);
                        if (endEl) endEl.textContent = formatPressure(range.last);
                    }
                });

                if (!type) return;
                var liters = window.dhTankWaterVolumeLiters(type, twins);
                if (!liters) return;
                // Gas actually consumed (Pablo, 2026-10-16: "subtract the
                // end pressure from the start pressure and then, depending
                // on the tank, calculate the actual consumption") - delta
                // bar x the cylinder's water volume, summed over a pair;
                // the same figure DiveGasStats::stats() hands the app.
                // Imperial shows it as free-gas cuft (liters / 28.3168).
                var consumedL = 0, consumedKnown = false;
                var rmvTotal = 0, rmvKnown = false;
                tankIndices.forEach(function (ti) {
                    var range = tankPressureRange(ti);
                    if (range) { consumedL += Math.max(0, range.first - range.last) * liters; consumedKnown = true; }
                    var sac = avgSacBar(ti);
                    if (sac !== null) { rmvTotal += sac * liters; rmvKnown = true; }
                });
                if (volumeEl) {
                    if (consumedKnown) {
                        volumeEl.textContent = window.dhUnits.getUnit() === 'imperial'
                            ? window.dhTankLitersToCuft(consumedL).toFixed(1) + ' cuft'
                            : Math.round(consumedL) + ' L';
                    } else {
                        volumeEl.textContent = '—';
                    }
                }
                if (rmvEl && rmvKnown) rmvEl.textContent = formatRmv(rmvTotal);
            });

            // Sets a <x-dh-select>'s value/label/selection the same way
            // dh-select.js's own option-click handler does - for
            // re-pointing the tank-size picker at a different tank
            // (below) without a page reload.
            function setDhSelectValue(wrapEl, value, label) {
                var hidden = wrapEl.querySelector('input[type="hidden"]');
                var valueSpan = wrapEl.querySelector('.dh-select-value');
                var menu = wrapEl.querySelector('.dh-select-menu');
                if (!hidden || !valueSpan) return;
                hidden.value = value;
                valueSpan.textContent = label;
                if (menu) {
                    menu.querySelectorAll('li').forEach(function (li) {
                        li.classList.toggle('is-selected', li.dataset.value === String(value));
                    });
                }
            }

            // Mutual exclusivity (Pablo, 2026-10-15: "if a tank was
            // already assigned to a gas, then it cannot be assigned to
            // another gas") - live feedback only; the real enforcement is
            // server-side in LogbookController@updateConditions, since
            // this alone can't stop e.g. two browser tabs from both
            // picking the same tank.
            function refreshGasTankMutualExclusivity() {
                var selects = Array.prototype.slice.call(document.querySelectorAll('input[type="hidden"][id^="gasTankChoice"]'));
                var chosen = selects.map(function (s) { return s.value; }).filter(function (v) { return v !== ''; });
                selects.forEach(function (hidden) {
                    var wrap = hidden.closest('.dh-select');
                    var menu = wrap && wrap.querySelector('.dh-select-menu');
                    if (!menu) return;
                    menu.querySelectorAll('li[data-value]').forEach(function (li) {
                        var v = li.dataset.value;
                        if (!v) return; // placeholder always available
                        li.hidden = chosen.indexOf(v) !== -1 && hidden.value !== v;
                    });
                });
            }
            refreshGasTankMutualExclusivity();

            // Tank-with-AI pick (Pablo, 2026-10-15: "Tank with AI? is the
            // right question...we cannot forget the tank size, otherwise
            // we cannot calculate RMV"; follow-up: "even if there's no
            // more AI info available for another tank, allow the user to
            // select the tank size...at least he can log what size tank
            // he used") - the size picker stays visible either way now,
            // this just re-points its form field name at whichever
            // storage the current pick calls for: conditions.tank_config
            // (keyed by tank index) once a real tank is chosen, else the
            // gas-keyed conditions.gas_tank_size_choice fallback. A type
            // the diver already picked is kept across that switch rather
            // than cleared, UNLESS the newly-chosen tank already has its
            // own known size (window.dhDiveTankConfig, same global the
            // chart's own RMV init reads) - that tank's own history wins.
            document.querySelectorAll('input[type="hidden"][id^="gasTankChoice"]').forEach(function (hidden) {
                hidden.addEventListener('change', function () {
                    var row = hidden.closest('.dh-dive-gas-row');
                    var wrap = row && row.querySelector('[data-tank-size-wrap]');
                    if (wrap) {
                        var newIndex = hidden.value;
                        var gasIndex = row.dataset.gasIndex || row.dataset.rowKey;
                        wrap.dataset.tankIndex = newIndex;
                        var sizeWrap = wrap.querySelector('.dh-select');
                        var sizeHidden = sizeWrap && sizeWrap.querySelector('input[type="hidden"]');
                        var twinsInput = wrap.querySelector('[data-tank-twins-input]');
                        if (sizeHidden) {
                            var fieldBase = newIndex
                                ? 'conditions[tank_config][' + newIndex + ']'
                                : 'conditions[gas_tank_size_choice][' + gasIndex + ']';
                            sizeHidden.name = fieldBase + '[type]';
                            if (twinsInput) twinsInput.name = fieldBase + '[twins]';
                            var known = newIndex ? (window.dhDiveTankConfig || {})[newIndex] : null;
                            if (known && known.type) {
                                setDhSelectValue(sizeWrap, known.type, known.type);
                                if (twinsInput) twinsInput.checked = !!known.twins;
                            }
                        }
                    }
                    refreshGasTankMutualExclusivity();
                });
            });

            // Section-level pencil/Save/Cancel (Pablo, 2026-10-15: "use a
            // pencil Icon to edit the whole Gases used section...I don't
            // want to see the dropdown unless I'm in edit mode. Then show
            // button save") - every row's [data-view]/[data-edit] toggles
            // together, same shape the bigger cards' own edit-to-unlock
            // already uses, just applied to all rows in this section at
            // once instead of one card.
            var editBtn = document.querySelector('[data-gases-edit-btn]');
            var saveBtn = document.querySelector('[data-gases-save]');
            var cancelBtn = document.querySelector('[data-gases-cancel]');
            if (editBtn) {
                editBtn.addEventListener('click', function () {
                    // The pencil lives in the collapsible header (Pablo,
                    // 2026-10-15: "if the card is collapsed and the pencil
                    // icon clicked, expand the card in edit mode") - go
                    // through the header's own toggle so the chart-resize
                    // on open runs too.
                    var gasesBody = document.getElementById('diveGasesBody');
                    var gasesHead = document.querySelector('[aria-controls="diveGasesBody"]');
                    if (gasesBody && gasesBody.hidden && gasesHead) gasesHead.click();
                    document.querySelectorAll('#diveGasRows [data-view]').forEach(function (el) { el.hidden = true; });
                    document.querySelectorAll('#diveGasRows [data-edit]').forEach(function (el) { el.hidden = false; });
                    editBtn.hidden = true;
                    if (saveBtn) saveBtn.hidden = false;
                    if (cancelBtn) cancelBtn.hidden = false;
                });
            }
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    document.querySelectorAll('#diveGasRows [data-view]').forEach(function (el) { el.hidden = false; });
                    document.querySelectorAll('#diveGasRows [data-edit]').forEach(function (el) { el.hidden = true; });
                    if (editBtn) editBtn.hidden = false;
                    saveBtn.hidden = true;
                    cancelBtn.hidden = true;
                });
            }

            // Drag-to-reorder (Pablo, 2026-10-15: "I will also put the
            // rows in Kanban so the user can sort them in any particular
            // order - just for visuals") - plain HTML5 drag and drop, no
            // library; saves immediately on drop rather than waiting for
            // the section's own Save button, since it's display order
            // only and not gated behind edit mode the way the real data
            // (tank/role/delete) is.
            var gasRowsContainer = document.getElementById('diveGasRows');
            if (gasRowsContainer) {
                var draggedRow = null;
                var gasCsrfToken = (document.querySelector('input[name="_token"]') || {}).value;

                function saveGasRowOrder() {
                    if (!window.dhDiveConditionsUrl) return;
                    var body = new URLSearchParams();
                    body.set('_token', gasCsrfToken);
                    body.set('form', 'gas_row_order');
                    gasRowsContainer.querySelectorAll('.dh-dive-gas-row').forEach(function (row) {
                        body.append('conditions[gas_row_order][]', row.dataset.rowKey);
                    });
                    fetch(window.dhDiveConditionsUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                        body: body.toString(),
                    });
                }

                gasRowsContainer.querySelectorAll('.dh-dive-gas-row').forEach(function (row) {
                    row.addEventListener('dragstart', function (e) {
                        draggedRow = row;
                        row.classList.add('is-dragging');
                        if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
                    });
                    row.addEventListener('dragend', function () {
                        row.classList.remove('is-dragging');
                        draggedRow = null;
                        saveGasRowOrder();
                    });
                    row.addEventListener('dragover', function (e) {
                        e.preventDefault();
                        if (!draggedRow || draggedRow === row) return;
                        var rect = row.getBoundingClientRect();
                        var before = (e.clientY - rect.top) < rect.height / 2;
                        gasRowsContainer.insertBefore(draggedRow, before ? row : row.nextSibling);
                    });
                });
            }
        }
    });
})();
