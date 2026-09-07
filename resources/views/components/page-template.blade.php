@props(['bodyClass', 'SEO'])
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
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets') }}/img/apple-icon.png">
  <!--<link rel="icon" type="image/png" href="{{ asset('assets') }}/img/logos/logo_divershub_white.png">-->
  <link rel="icon" href="{{ asset('assets') }}/img/favicon.ico">
  <!-- Installable web app: manifest, theme color and home screen icon. Groundwork for the offline PWA; no service worker yet. -->
  <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
  <meta name="theme-color" content="#0b2a3a">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Divers Hub">
  <link rel="apple-touch-icon" href="{{ asset('assets') }}/img/pwa/icon-192.png">

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
    $ogImage = $SEO['image'] ?? asset('assets/img/diveHub-login.jpg');
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
  <!-- Divers Hub tokens and shared components (chips, legend). Versioned by release so caches refresh. -->
  <link href="{{ asset('assets') }}/css/divershub.css?v={{ config('divehub.version') }}" rel="stylesheet" />
</head>
<body class="{{ $bodyClass }}">

{{ $slot }}

{{-- Guest account prompt, once per page, only when the visitor is the shared guest user. --}}
<x-guest-modal />

<script src="{{ asset('assets') }}/js/core/popper.min.js"></script>
<script src="{{ asset('assets') }}/js/core/bootstrap.min.js"></script>
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
