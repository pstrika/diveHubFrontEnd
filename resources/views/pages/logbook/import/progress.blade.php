@php $total = $done + $remaining; $pct = $total ? (int) round($done * 100 / $total) : 100; @endphp
<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Import: importing" icon="upload_file" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">

                    <div class="dh-import-steps"><span>1 Review</span><span>2 Sites</span><span class="is-active">3 Done</span></div>

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="card-body text-center py-4">
                            <h3 class="h5 mb-2">Importing {{ $import->filename }}</h3>
                            <p class="text-sm text-secondary mb-3">{{ $done }} of {{ $total }} dives added. Keep this page open - it continues on its own.</p>
                            <div class="dh-import-progress"><div class="dh-import-progress-bar" style="width: {{ $pct }}%;"></div></div>
                            <form method="POST" action="{{ route('Logbook.import.run', $import->id) }}" id="importNext" class="mt-3">
                                @csrf
                                <noscript><button type="submit" class="dh-btn dh-btn-primary">Continue</button></noscript>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>setTimeout(function () { document.getElementById('importNext').submit(); }, 150);</script>
    @endpush
</x-page-template>
