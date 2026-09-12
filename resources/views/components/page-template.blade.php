@props(['bodyClass', 'SEO'])
@php
    /*
     * Frozen pages are the ones a client embeds in their own site, so they must
     * render exactly as they did before the redesign: no tokens stylesheet, no
     * shared script, no guest prompt, no add to home screen bar, no release
     * stamp, and the head colours they had before. See App\Support\EmbeddedPage.
     */
    $dhFrozen = \App\Support\EmbeddedPage::isFrozen();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-JX0ZQN5ZK6"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-JX0ZQN5ZK6');
  </script>
  <!-- Google Analytics Code -->
  @sendGA4ClientID
  <!-- </head> -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
  <!--<link rel="icon" type="image/png" href="{{ asset('assets') }}/img/logos/logo_divershub_white.png">-->
  <link rel="icon" href="{{ asset('assets') }}/img/favicon.ico">
  <!-- PWA - manifest lives on main now so the redesign branch can build a
       full install/PWA experience on top without starting from scratch.
       apple-touch-icon is what iOS actually uses for the Home Screen icon
       (manifest.json icons are ignored by iOS Safari for this purpose). -->
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets') }}/img/pwa/apple-touch-icon.png">
  {{-- Redesign shell colour, except on frozen pages which keep the colour they had. --}}
  <meta name="theme-color" content="{{ $dhFrozen ? \App\Support\EmbeddedPage::LEGACY_THEME_COLOR : '#0b2a3a' }}">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Divers Hub">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="application-name" content="Divers Hub">
  <meta name="msapplication-TileColor" content="{{ $dhFrozen ? \App\Support\EmbeddedPage::LEGACY_THEME_COLOR : '#0b2a3a' }}">
  <meta name="msapplication-TileImage" content="{{ asset('assets') }}/img/pwa/icon-192.png">

  @unless($dhFrozen)
  {{-- Boot splash CSS, inline so it applies before the external stylesheet
       loads. display-mode:standalone is what keeps it off in a normal
       browser tab - no JS involved in that decision. --}}
  <style>
    #dh-splash { display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; background: #0b2a3a; transition: opacity .25s ease; }
    #dh-splash img { width: 88px; height: 88px; border-radius: 22px; animation: dhSplashPulse 1.1s ease-in-out infinite; }
    #dh-splash.dh-splash-hide { opacity: 0; pointer-events: none; }
    @keyframes dhSplashPulse { 0%, 100% { transform: scale(1); opacity: .82; } 50% { transform: scale(1.08); opacity: 1; } }
    @media (display-mode: standalone) {
      #dh-splash { display: flex; }
    }
  </style>
  @endunless

  <title>{{ $SEO["title"] ?? "Divers Hub - your one stop for diving in FL!" }}</title>
 
  <!-- Google AdSense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9073316730673872" crossorigin="anonymous"></script>

  <!--     Metas    -->
  <meta name="description" content="{{ $SEO["desc"] ??  "All you need to know about scuba diving in South Florida"}}">
  <meta name="keywords" content="{{ $SEO["keywords"] ?? "divers-hub, diving, scuba, diving florida, scuba florida, dive" }}">
  
  @if(!empty($SEO['canonical']))
    <link rel="canonical" href="{{ $SEO['canonical'] }}">
  @endif

  @if(!empty($SEO['robots']))
    <meta name="robots" content="{{ $SEO['robots'] }}">
  @elseif(empty($SEO['title']))
    <meta name="robots" content="noindex, nofollow">
  @endif

  <!--     Open Graph / Twitter Card     -->
  @php
    $ogTitle = $SEO['title'] ?? 'Divers Hub - your one stop for diving in FL!';
    $ogDesc = $SEO['desc'] ?? 'All you need to know about scuba diving in South Florida';
    $ogUrl = $SEO['canonical'] ?? url()->current();
    // Default social preview: a 1200x630 web copy (165 KB) of the 17 MB login photo that used to be served here.
    $ogImage = $SEO['image'] ?? asset($dhFrozen ? \App\Support\EmbeddedPage::LEGACY_OG_IMAGE : 'assets/img/og-default.jpg');
  @endphp
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Divers Hub">
  <meta property="og:title" content="{{ $ogTitle }}">
  <meta property="og:description" content="{{ $ogDesc }}">
  <meta property="og:url" content="{{ $ogUrl }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $ogTitle }}">
  <meta name="twitter:description" content="{{ $ogDesc }}">
  <meta name="twitter:image" content="{{ $ogImage }}">

  <!--     Fonts and icons     -->
  <!-- Non-critical fonts/icon sets: preload without blocking the initial render, then swap to stylesheet once loaded -->
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" as="style" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  </noscript>
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets') }}/css/nucleo-icons.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets') }}/css/material-dashboard.css?v=3.0.1" rel="stylesheet" />
  @unless($dhFrozen)
  <!-- Divers Hub tokens and shared components (chips, legend). Versioned by release so caches refresh. -->
  <link href="{{ asset('assets') }}/css/divershub.css?v={{ config('divehub.version') }}" rel="stylesheet" />
  @endunless
</head>
<body class="{{ $bodyClass }}">

@unless($dhFrozen)
<x-pwa-splash />
@endunless

{{ $slot }}

@unless($dhFrozen)
{{-- Guest account prompt, once per page, only when the visitor is the shared guest user. --}}
<x-guest-modal />
{{-- Add to home screen bar (Android/desktop) and its iOS equivalent, a proper
     modal since iOS has no install API to trigger from a bar's button. Both
     shown by divershub.js. --}}
<x-install-prompt />
<x-ios-install-modal />
{{-- The Me tab's menu rows, on every page now (not just the dashboard) so a
     diver can always reach their calendar/profile/etc without navigating
     home first. Members only - a guest has no account to manage. --}}
@auth
    @if(auth()->user()->isNotGuest())
        <x-shell.me-rows />
    @endif
@endauth
{{-- Every page builds its own <footer class="footer"> inside its own
     content, so it lands wherever that page put it - usually right after
     the cards, ahead of the Me rows above. Moving it here in the DOM (not
     just visually) puts it after the Me rows, which is where "the very
     bottom of the page" actually means once those rows exist. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var footer = document.querySelector('footer.footer');
        var meRows = document.querySelector('.dh-me-rows');
        if (footer && meRows) {
            meRows.insertAdjacentElement('afterend', footer);
        }
    });
</script>
@endunless
{{-- Pablo's mobile bottom nav hook (main 9.22.0); the old sidebar pushes into it, the shell has its own tab bar. --}}
@stack('bottom-nav')

<script src="{{ asset('assets') }}/js/core/popper.min.js"></script>
<script src="{{ asset('assets') }}/js/core/bootstrap.min.js"></script>
@unless($dhFrozen)
<!-- Divers Hub shared behaviour (chip row arrows, add to home screen). Versioned by release like the stylesheet. -->
<script src="{{ asset('assets') }}/js/divershub.js?v={{ config('divehub.version') }}" defer></script>
@endunless
<script src="{{ asset('assets') }}/js/plugins/smooth-scrollbar.min.js"></script>
<!-- Kanban scripts -->
<script src="{{ asset('assets') }}/js/plugins/dragula/dragula.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/jkanban/jkanban.js"></script>
@stack('js')
<script>
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    var options = {
      damping: '0.5',
    }
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
  }
</script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ asset('assets') }}/js/material-dashboard.min.js?v=3.0.1"></script>
</body>
</html>
