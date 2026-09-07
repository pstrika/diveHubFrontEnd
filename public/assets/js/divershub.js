/*
 * Divers Hub shared behaviour. No dependencies; loaded by page-template after
 * Bootstrap. Everything here is an enhancement: the pages work without it.
 */
(function () {
  'use strict';

  /*
   * Filter chip rows scroll sideways on phones. A swipeable row is not obvious,
   * so when a row overflows we wrap it and add arrows on both edges that scroll
   * the row by most of its width. Each arrow hides when its direction is exhausted.
   */
  function enhanceChipRows() {
    document.querySelectorAll('.dive-filter-chips').forEach(function (row) {
      if (row.parentElement.classList.contains('dh-chip-scroll')) return;
      if (row.scrollWidth <= row.clientWidth + 4) return;

      var wrap = document.createElement('div');
      wrap.className = 'dh-chip-scroll';
      row.parentNode.insertBefore(wrap, row);
      wrap.appendChild(row);

      // One arrow per side. Each hides when the row cannot scroll further that way.
      var makeArrow = function (side) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dh-chip-more dh-chip-more-' + side;
        btn.setAttribute('aria-label', side === 'left' ? 'Scroll filters back' : 'Show more filters');
        btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">chevron_' + side + '</span>';
        btn.addEventListener('click', function () {
          row.scrollBy({ left: row.clientWidth * (side === 'left' ? -0.7 : 0.7), behavior: 'smooth' });
        });
        wrap.appendChild(btn);
        return btn;
      };
      var back = makeArrow('left');
      var more = makeArrow('right');

      var update = function () {
        back.hidden = row.scrollLeft <= 4;
        more.hidden = row.scrollLeft + row.clientWidth >= row.scrollWidth - 4;
      };
      row.addEventListener('scroll', update, { passive: true });
      update();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceChipRows);
  } else {
    enhanceChipRows();
  }
  window.addEventListener('resize', enhanceChipRows);
})();
