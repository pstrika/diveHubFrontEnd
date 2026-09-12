<section class="dh-panel">
    <div class="dh-panel-head-row">
        <h2 class="dh-panel-title mb-0"><i class="fa-brands fa-facebook" aria-hidden="true"></i> Facebook feed</h2>
        <a href="https://facebook.com/{{ $group->fb_page_id }}" target="_blank" rel="noopener" class="dh-btn dh-btn-ghost-dark">
            <span class="material-icons-round" aria-hidden="true">open_in_new</span>View Page
        </a>
    </div>
    <div style="max-height: 320px; overflow-y: auto;">
        @foreach($fbFeed as $post)
            <div class="dh-fb-post">
                @if(!empty($post['full_picture']))
                    <img src="{{ $post['full_picture'] }}" alt="" class="dh-fb-post-img">
                @endif
                <div class="dh-fb-post-body">
                    @if(!empty($post['message']))
                        <p class="dh-fb-post-text">{{ \Illuminate\Support\Str::limit($post['message'], 140) }}</p>
                    @endif
                    <p class="dh-fb-post-time">{{ \Carbon\Carbon::parse($post['created_time'])->diffForHumans() }}</p>
                    @if(!empty($post['permalink_url']))
                        <a href="{{ $post['permalink_url'] }}" target="_blank" rel="noopener" class="dh-fb-post-link">View on Facebook</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
