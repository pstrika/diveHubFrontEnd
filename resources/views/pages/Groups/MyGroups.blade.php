<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO ?? []">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-auth.navbars.navs.auth pageTitle="Diving Group"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ asset('assets') }}/img/illustrations/beach_diving.webp');">
                <span class="mask bg-gradient-info opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1 clearfix">
                    <div style="float: left;">
                        <h1 class="card-title text-info mx-3 mt-0">Diving Group</h1>
                    </div>
                    <div style="float: right;" class="mx-3">
                        <a href="{{ route('Groups.create') }}" class="btn bg-gradient-info">
                            <i class="material-icons text-sm align-middle me-1">add</i> Create a Group
                        </a>
                    </div>
                </div>
            </div>

            <x-flash-toast />

            @if($invites->isNotEmpty())
            <div class="row mx-1">
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-warning shadow py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Pending Invites</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table align-items-center mb-0">
                                <tbody>
                                    @foreach($invites as $invite)
                                        <tr style="border-bottom: 1px solid #D3D3D3;">
                                            <td class="align-middle text-left text-md"><b>{{ $invite->group->name }}</b></td>
                                            <td class="align-middle text-end">
                                                <form method="POST" action="{{ route('Groups.invites.accept', ['member' => $invite->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm bg-gradient-success">Accept</button>
                                                </form>
                                                <form method="POST" action="{{ route('Groups.invites.decline', ['member' => $invite->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm bg-gradient-secondary">Decline</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row mx-1">
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Your Groups</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($groups->isEmpty())
                                <p class="text-secondary mb-0">You're not in any groups yet. Create one to get started!</p>
                            @else
                                <table class="table align-items-center mb-0">
                                    <tbody>
                                        @foreach($groups as $group)
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="align-middle text-left text-md">
                                                    <a href="{{ route('Groups.show', ['group' => $group->slug]) }}" class="d-flex align-items-center text-dark">
                                                        <div class="avatar avatar-sm me-2">
                                                            @if($group->avatar)
                                                                <img src="{{ asset('assets/' . $group->avatar) }}" alt="{{ $group->name }}" class="w-100 rounded-circle shadow-sm">
                                                            @else
                                                                <div class="w-100 h-100 rounded-circle bg-gradient-info d-flex align-items-center justify-content-center text-white text-sm">
                                                                    <i class="material-icons text-sm">groups</i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <b>{{ $group->name }}</b>
                                                    </a>
                                                </td>
                                                <td class="align-middle text-secondary text-sm">{{ $group->activeMembers()->count() }} members</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
