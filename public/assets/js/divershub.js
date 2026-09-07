/*
 * Divers Hub shared behaviour. No dependencies; loaded by page-template after
 * Bootstrap. Everything here is an enhancement: the pages work without it.
 */
(function () {
  'use strict';

  /*
   * Filter chip rows scroll sideways on phones. A swipeable row is not obvious,
   * so when a row overflows we wrap it and add a "more" arrow on the right edge
   * that scrolls the row by most of its width. The arrow hides at the end.
   */
  function enhanceChipRows() {
    document.querySelectorAll('.dive-filter-chips').forEach(function (row) {
      if (row.parentElement.classList.contains('dh-chip-scroll')) return;
      if (row.scrollWidth <= row.clientWidth + 4) return;

      var wrap = document.createElement('div');
      wrap.className = 'dh-chip-scroll';
      row.parentNode.insertBefore(wrap, row);
      wrap.appendChild(row);

      var more = document.createElement('button');
      more.type = 'button';
      more.className = 'dh-chip-more';
      more.setAttribute('aria-label', 'Show more filters');
      more.innerHTML = '<span class="material-icons-round" aria-hidden="true">chevron_right</span>';
      more.addEventListener('click', function () {
        row.scrollBy({ left: row.clientWidth * 0.7, behavior: 'smooth' });
      });
      wrap.appendChild(more);

      var update = function () {
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
