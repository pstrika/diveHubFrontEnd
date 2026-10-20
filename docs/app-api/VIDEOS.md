# Dive videos (app API)

Web 12.21.0 (2026-10-20). Videos live on Cloudflare Stream; the app uploads
straight to Cloudflare, never through divers-hub.com. All routes need the
bearer token. `GET /api/me/features` says whether the account has them:
`videos`, `max_videos_per_dive` (5), `max_video_seconds` (180),
`max_video_bytes` (1 GB).

Recommended: re-encode on the phone first (720p H.264, ~20 MB/min) so uploads
from a boat are realistic. Cloudflare refuses a video longer than 3 minutes
(it ends up `failed` with an error text).

## Video object

```json
{
  "uuid": "…", "dive_uuid": "…",
  "status": "uploading | processing | ready | failed", "error": null,
  "duration_seconds": 42, "width": 1920, "height": 1080, "size_bytes": 12345,
  "caption": null, "position": 1,
  "shared": null,            // null | pending | approved | rejected (site-page review)
  "player_url": "https://customer-xxx.cloudflarestream.com/<token>/iframe",
  "hls_url": "https://customer-xxx.cloudflarestream.com/<token>/manifest/video.m3u8",
  "thumbnail_url": "https://customer-xxx.cloudflarestream.com/<token>/thumbnails/thumbnail.jpg?time=1s&height=360",
  "urls_expire_at": "2026-10-20T13:00:00+00:00",   // null until ready; refetch after
  "updated_at": "…"
}
```

`player_url`, `hls_url` and `thumbnail_url` are null until `status` is `ready`.
They carry an owner-only token good for about an hour: fetch the video (or the
dive) again for fresh ones. `hls_url` plays natively in AVPlayer.

## Upload

1. `POST /api/dives/{dive_uuid}/videos` JSON `{name?, size_bytes?, caption?, uuid?}`
   → 201 `{video, upload_url, upload_kind}`.
   - With `size_bytes`: `upload_kind` = `tus`. Resumable: `PATCH upload_url`
     with `Tus-Resumable: 1.0.0`, `Upload-Offset`, body
     `application/offset+octet-stream`, chunks a multiple of 256 KiB (5–50 MB
     is fine). Any tus client works (TUSKit on iOS).
   - Without: `upload_kind` = `direct`. One multipart `POST upload_url` with a
     `file` field, up to 200 MB.
   - 422 when the dive already has 5 videos or the size is over 1 GB.
2. `POST /api/videos/{uuid}/uploaded` → `{video}` (status `processing`).
3. Poll `GET /api/videos/{uuid}` every few seconds until `ready` or `failed`
   (usually 1–2 minutes). `GET /api/dives/{uuid}` also carries `videos`.

## Other calls

- `GET /api/dives/{dive_uuid}/videos` → `{videos: [...]}`.
- `PATCH /api/videos/{uuid}` `{caption?, position?}` → `{video}`.
- `DELETE /api/videos/{uuid}` → 204. Also deletes it on Cloudflare and off the
  site page if it was shared.
- `POST /api/videos/{uuid}/share` `{share: true|false}` → `{video}`. Offers a
  `ready` video for the dive's site page (admin review, like photos; `shared`
  tells the state) or takes it back. 422 when the dive has no Divers Hub site
  or the video isn't ready yet.

Every change logs `updated` for the dive in the change feed.
