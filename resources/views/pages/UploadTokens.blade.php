<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Dive Computer Upload" icon="watch" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-8 col-12 m-auto pt-4">

                    <p class="text-sm">
                        Generate a code here, then paste it into the Divers Hub dive-computer
                        uploader app once. The app uses it to upload the dives it downloads
                        from your dive computer into your logbook - you won't need to sign in
                        again inside the app itself.
                    </p>

                    @if ($newToken)
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body">
                                <h3 class="mb-2">Your new upload code</h3>
                                <p class="text-sm">
                                    Copy this now - it's shown only once and can't be retrieved
                                    again later. If you lose it, just generate a new one below.
                                </p>
                                <code class="d-block p-3" style="background: var(--dh-paper-alt, #f3f3f3); border-radius: 8px; word-break: break-all;">{{ $newToken }}</code>
                            </div>
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="mb-2">Generate a new code</h3>
                            <form method="POST" action="{{ route('UploadTokens.store') }}" class="d-flex" style="gap: 10px;">
                                @csrf
                                <input type="text" name="device_name" class="form-control" placeholder="e.g. Pablo's iPhone" required maxlength="60">
                                <button type="submit" class="dh-btn dh-btn-primary" style="white-space: nowrap;">Generate code</button>
                            </form>
                        </div>
                    </div>

                    @if ($tokens->isNotEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body">
                                <h3 class="mb-2">Active codes</h3>
                                @foreach ($tokens as $token)
                                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom: 1px solid var(--dh-line, #e5e5e5);">
                                        @php
                                            // "app:" tokens (Pablo, 2026-10-15) come from the in-app
                                            // sign-in flow, not this page's own "Generate a new code"
                                            // form - same revoke UI either way, just a different mint
                                            // path worth a small label so it's not confused for one
                                            // generated here.
                                            $isAppSignIn = str_starts_with($token->name, 'app:');
                                            $deviceName = \Illuminate\Support\Str::after($token->name, $isAppSignIn ? 'app:' : 'uploader:');
                                        @endphp
                                        <div>
                                            <div>{{ $deviceName }} @if ($isAppSignIn) <span class="text-xs text-secondary">(signed in)</span> @endif</div>
                                            <div class="text-xs text-secondary">
                                                {{ $token->last_used_at ? 'Last used ' . $token->last_used_at->diffForHumans() : 'Never used' }}
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('UploadTokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke this code? The app using it will stop being able to upload dives.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dh-btn dh-btn-ghost-dark">Revoke</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>
</x-page-template>
