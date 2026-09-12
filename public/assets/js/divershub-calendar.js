/*
 * Shared FullCalendar setup for every calendar in the app (My Dashboard, an
 * operator's page, a group's calendar, the themed trip calendars): phones get
 * a 3 day view, tablets a week, desktop a month, so a diver sees the same
 * shape of calendar wherever one shows up.
 *
 * Colors: events are colored with FullCalendar's own `color`/`backgroundColor`
 * per event, not a CSS class. dayGrid views (month/week/3-day, all used here)
 * render a timed event as a small colored dot next to its title rather than a
 * solid block, so the color only needs to carry the dot, not stay readable as
 * a full-width background - keep using that per-event `color`, not a
 * bg-gradient-* utility class, when adding a new calendar.
 */
function getResponsiveView() {
    var width = window.innerWidth;
    if (width >= 1200) return 'dayGridMonth';  // desktop
    if (width >= 768) return 'dayGridWeek';    // tablet
    return 'dayGridThreeDay';                  // phone
}

var dhResponsiveViews = {
    dayGridThreeDay: {
        type: 'dayGrid',
        duration: { days: 3 },
        buttonText: '3 day',
        titleFormat: { month: 'long', year: 'numeric', day: 'numeric' }
    },
    month: { titleFormat: { month: 'long', year: 'numeric' } },
    week: { titleFormat: { month: 'long', year: 'numeric', day: 'numeric' } }
};

/*
 * Prev/next for a server-paginated calendar (the themed trip calendars, My
 * Calendar): steps the anchor day by whatever the active view's own width is
 * - a month on desktop, a week on tablets, 3 days on phones - clamped so it
 * never goes before today, then loads a fresh page for that day. The month
 * (or week, or 3 days) a diver sees and the trip list rendered below it are
 * both server-rendered for that URL, so this is a real navigation, not a
 * client-side view change.
 */
function dhStepCalendarAnchor(calendarUrl, anchor, today, direction) {
    var view = getResponsiveView();
    var d = new Date(anchor + 'T00:00:00');
    var t = new Date(today + 'T00:00:00');
    if (view === 'dayGridMonth') {
        var day = d.getDate();
        d.setDate(1);
        d.setMonth(d.getMonth() + direction);
        var lastDay = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        d.setDate(Math.min(day, lastDay));
    } else if (view === 'dayGridWeek') {
        d.setDate(d.getDate() + direction * 7);
    } else {
        d.setDate(d.getDate() + direction * 3);
    }
    if (d < t) { d = t; }
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day2 = String(d.getDate()).padStart(2, '0');
    window.location.href = calendarUrl + '/' + y + '-' + m + '-' + day2;
}

/** Wires a calendar's prev/next buttons to dhStepCalendarAnchor(). */
function dhWireCalendarNav(prevBtnId, nextBtnId, calendarUrl, anchor, today) {
    document.getElementById(prevBtnId).addEventListener('click', function () {
        dhStepCalendarAnchor(calendarUrl, anchor, today, -1);
    });
    document.getElementById(nextBtnId).addEventListener('click', function () {
        dhStepCalendarAnchor(calendarUrl, anchor, today, 1);
    });
}
