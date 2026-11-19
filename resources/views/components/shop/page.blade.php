{{--
    Frame for every shop portal, shop access and home shop screen (Zach,
    2026-11-13): shell nav, page heading, the Beta note, and the one-line
    result of the last action (session "shop_status") or its errors.

    Usage: <x-shop.page title="Shop portal" icon="storefront"> ... </x-shop.page>
    $beta: the sentence next to the Beta pill; false to leave the note out.
--}}
@props(['title', 'icon' => 'storefront', 'beta' => 'This is new and still changing. Tell us what is wrong or missing with the chat button in the corner; it comes straight to us.'])

<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <link rel="stylesheet" href="{{ asset('assets/css/shop-portal.css') }}?m={{ @filemtime(public_path('assets/css/shop-portal.css')) }}">
        <meta name="robots" content="noindex">
        <x-shell.header :title="$title" :icon="$icon" :h1="true" />

        <div class="container-fluid py-0">
            <div class="dh-shop-wrap">
                @if($beta)
                    <div class="dh-shop-beta dh-shop-noprint"><span class="dh-shop-pill">Beta</span><span>{{ $beta }}</span></div>
                @endif
                @if(session('shop_status'))
                    <div class="dh-shop-status" role="status">{{ session('shop_status') }}</div>
                @endif
                @if($errors->any())
                    <div class="dh-shop-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </main>
</x-page-template>
