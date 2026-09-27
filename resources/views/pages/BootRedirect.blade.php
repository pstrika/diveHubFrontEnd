<!doctype html>
{{--
    Relaunching the installed PWA at "/" with a valid session used to mean
    an invisible 302 (no body at all) before the destination page's own,
    much heavier HTML/CSS ever started loading - a blank screen for a
    couple of seconds (Pablo, 2026-09-19: "when I open the app, it takes a
    couple of seconds to load...we need to show the splash IMMEDIATELY").

    This page is deliberately tiny and dependency-free (no page-template,
    no external stylesheet, no fonts) so it paints almost instantly no
    matter how slow the network is, then hands off to the real destination
    client-side. Same splash visual as <x-pwa-splash> for continuity, but
    it isn't that component - this response has to exist as fast as
    possible and can't wait on anything the shared layout pulls in.
--}}
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Divers Hub</title>
<style>
  html, body { margin: 0; height: 100%; background: #0b2a3a; }
  #dh-splash { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: #0b2a3a; }
  #dh-splash img { width: 88px; height: 88px; border-radius: 22px; animation: dhSplashPulse 1.1s ease-in-out infinite; }
  @keyframes dhSplashPulse { 0%, 100% { transform: scale(1); opacity: .82; } 50% { transform: scale(1.08); opacity: 1; } }
</style>
</head>
<body>
<div id="dh-splash" aria-hidden="true">
  <img src="{{ asset('assets/img/pwa/icon-512.png') }}" alt="">
</div>
<script>location.replace({{ \Illuminate\Support\Js::from($to) }});</script>
</body>
</html>
