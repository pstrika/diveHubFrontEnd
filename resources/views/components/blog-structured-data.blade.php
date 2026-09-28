{{--
    BlogPosting structured data (SEO audit, 2026-09-28, finding #3: "blog
    posts have no Article schema, show no publish date"). Same
    json_encode convention as site-structured-data.blade.php. Skipped
    entirely for an unsaved preview - there's no real canonical or
    published_at yet to describe.
--}}
@props(['post'])

@php
    $articleLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'mainEntityOfPage' => route('Blog.show', $post->slug),
        'datePublished' => optional($post->published_at)->toAtomString(),
        'dateModified' => optional($post->updated_at)->toAtomString(),
        'author' => ['@type' => 'Organization', 'name' => 'Divers Hub', 'url' => url('/')],
        'publisher' => ['@id' => url('/') . '/#org'],
    ];
    if ($post->cover_image) {
        $articleLd['image'] = [asset($post->cover_image)];
    }
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($articleLd), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
