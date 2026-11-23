{{--
    One frame for every error page (Zach, 2026-11-19: the 404 still had the
    old logo and the old photo-background style).

    It is deliberately plain: the current round logo in the top bar, one
    card, one sentence about what happened and where to go next. No shell
    nav and no route() calls, because this renders when something has
    already gone wrong: every link is a fixed address, and nothing here
    needs the database or a signed-in user.

    Usage:
      <x-error-page code="404" icon="explore_off" title="We can't find that page">
          The link may be old, or the page may have moved.
          <x-slot:actions> ...buttons... </x-slot:actions>
      </x-error-page>
--}}
@props(['code' => null, 'icon' => 'error_outline', 'title', 'label' => null])

<x-page-template bodyClass='dh-shell bg-gray-200'>
    <header class="dh-topbar">
        <div class="dh-topbar-inner">
            <a class="dh-brand" href="{{ url('/') }}" aria-label="Divers Hub home">
                <img src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="" width="34" height="34">
                <span>Divers Hub</span>
            </a>
        </div>
    </header>

    <main class="main-content mt-0">
        <div class="container-fluid py-0 dh-board">
            <section class="dh-error">
                <span class="dh-error-icon" aria-hidden="true"><span class="material-icons-round">{{ $icon }}</span></span>
                {{-- $label swaps the "Error 404" line for plain words on pages that are not errors. --}}
                <p class="dh-error-code">{{ $label ?? 'Error ' . $code }}</p>
                <h1 class="dh-error-title">{{ $title }}</h1>
                <p class="dh-error-text">{{ $slot }}</p>
                <div class="dh-error-actions">
                    {{ $actions ?? '' }}
                </div>
            </section>
        </div>
    </main>
</x-page-template>
