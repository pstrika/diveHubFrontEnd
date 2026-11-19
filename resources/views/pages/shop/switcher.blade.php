{{-- Shop portal: pick a shop. Shown to someone who manages several, and to platform admins (every operator). --}}
<x-shop.page title="Shop portal">
    <section class="dh-panel">
        <h2 class="dh-panel-title">{{ $isPlatformAdmin ? 'Every shop (you are a Divers Hub admin)' : 'Your shops' }}</h2>
        @if($isPlatformAdmin)
            <p class="dh-shop-muted">To give a shop its own login, use <a href="{{ route('shop-access.index') }}">Shop access</a>.</p>
        @endif
        <div class="dh-shop-scroll">
            <table class="dh-shop-table">
                <tbody>
                @foreach($operators as $op)
                    <tr>
                        <td><a href="{{ route('shop-portal.overview', ['operator' => $op->id]) }}"><b>{{ $op->operatorName }}</b></a></td>
                        <td class="dh-shop-muted">{{ $op->cityAddress }}</td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.overview', ['operator' => $op->id]) }}">Manage</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-shop.page>
