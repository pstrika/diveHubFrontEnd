<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="groups" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Create a group" />
        <div class="container-fluid py-0 dh-board">

            <section class="dh-panel">
                @if(isset($errors) && $errors->any())
                    <div class="dh-form-error">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('Groups.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Group name</label>
                        <input type="text" name="name" class="form-control border" value="{{ old('name') }}" required minlength="3" maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control border" rows="4" maxlength="2000">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="dh-btn dh-btn-primary">Create group</button>
                        <a href="{{ route('MyGroups') }}" class="dh-btn dh-btn-ghost-dark">Cancel</a>
                    </div>
                </form>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
