<div class="card mt-3 mb-4">
    <div class="card-header p-0 mt-n4 mx-3">
        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1 d-flex justify-content-between align-items-center">
            <h2 class="card-title text-white mx-4 mb-0"><i class="fa-brands fa-facebook align-middle me-2"></i>Facebook Feed</h2>
            <a href="https://facebook.com/{{ $group->fb_page_id }}" target="_blank" rel="noopener" class="btn btn-sm bg-white text-info me-3 mb-0">
                <i class="material-icons text-sm align-middle">open_in_new</i> View Page
            </a>
        </div>
    </div>
    <div class="card-body p-3" style="max-height: 320px; overflow-y: auto;">
        @foreach($fbFeed as $post)
            <div class="d-flex mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                @if(!empty($post['full_picture']))
                    <img src="{{ $post['full_picture'] }}" style="width: 70px; height: 70px; object-fit: cover;" class="border-radius-md me-2 flex-shrink-0" alt="">
                @endif
                <div style="min-width: 0;">
                    @if(!empty($post['message']))
                        <p class="text-sm mb-1" style="overflow-wrap: break-word;">{{ \Illuminate\Support\Str::limit($post['message'], 140) }}</p>
                    @endif
                    <p class="text-xs text-secondary mb-1">
                        {{ \Carbon\Carbon::parse($post['created_time'])->diffForHumans() }}
                    </p>
                    @if(!empty($post['permalink_url']))
                        <a href="{{ $post['permalink_url'] }}" target="_blank" rel="noopener" class="text-xs">View on Facebook</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
