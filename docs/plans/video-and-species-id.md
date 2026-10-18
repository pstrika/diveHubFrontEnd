# Dive videos and species identification: plan

Pablo, 2026-10-18. Both are queued after the equipment work.

## 1. Dive videos (GoPro and phone clips)

**Decision pending:** a video service (recommended: Cloudflare Stream), or our own pipeline (Azure Blob plus an ffmpeg job). The plan below assumes Cloudflare Stream.

**Why not the web server:** it is one App Service P0v3 (1 vCPU, 4 GB). Transcoding there would slow the site for everyone. The request limit is about 25 MB, and GoPro 4K is roughly 350-450 MB per minute.

**What Pablo provides:** a Cloudflare account with Stream enabled, and an API token with Stream:Edit, stored as `CLOUDFLARE_ACCOUNT_ID` and `CLOUDFLARE_STREAM_TOKEN` in the App Service settings.

### Data
`dive_videos`, mirroring `dive_photos`:
- id, uuid, user_id, dive_id
- stream_uid (Cloudflare's id)
- status: uploading, processing, ready or failed
- duration_seconds, width, height
- caption, taken_at, position
- timestamps

### Limits
- 5 videos per dive.
- 3 minutes per video. Cloudflare's `maxDurationSeconds` makes it refuse longer ones with a clear message.
- 1 GB per upload.

### Upload flow (web)
1. The page asks our server for an upload link. The server calls Cloudflare's direct-creator-upload API, gets a one-time URL plus the stream uid, and creates the row with status `uploading`.
2. The browser uploads the file straight to Cloudflare. Use tus (resumable) for files over 200 MB, so a dropped connection doesn't restart the upload.
3. Cloudflare transcodes the video, then calls our webhook (`/webhooks/cloudflare-stream`, signature checked). The row becomes `ready` or `failed`, and the webhook stores duration, size and thumbnail.

### Upload flow (app)
- The same flow through the API: `POST /api/dives/{uuid}/videos` returns the upload URL and the video uuid.
- iOS first re-encodes to 720p H.264 with AVAssetExportSession (about 20 MB per minute), so uploads from a boat or a hotel are realistic.

### Privacy
- Use signed playback URLs (`requireSignedURLs`). Our server mints a short-lived token for the owner only, the same rule as photos.

### Dive page
- Videos sit in the Photos card, or a sibling "Videos" card: a poster thumbnail, and playback through Cloudflare's player (iframe) or HLS in a `<video>` tag.
- Caption and delete work as they do for photos. Deleting also deletes on Cloudflare.

### Sync
- Every change logs `updated` on the dive, so the app refetches.
- The dive JSON gains `videos: [...]`.

### Later
- Sharing a video to the site's public page, through review like photos.
- Species tags on videos.

### Cost
- About $5 per 1,000 minutes stored, plus about $1 per 1,000 minutes watched.

### Effort
- About 1-2 days for web and API.
- About 1 day for the iOS upload and compression, done by the iOS session.

## 2. Species identification from a photo

**Idea:** a "What's this?" button on a dive photo. AI looks at the picture and suggests species from our catalog. The diver confirms; nothing is ever tagged automatically.

**What we pay for today doesn't cover it:** Pablo's Claude subscription covers this coding assistant, not the website calling Claude. The site needs its own API key (Anthropic, or Google for Gemini), billed per use.

### Model
- Default: Claude Haiku 5.5 (`claude-haiku-5-5`). It's cheap and fast, and good enough to pick from a fixed list.
- Use Sonnet 5.5 if accuracy disappoints.
- Cost: well under a cent per photo at the 1600 px web copy. Cap it at, for example, 50 identifications per diver per day.

### Prompt design
- Send the photo plus our catalog (the diver's visible species: common name, scientific name and group, about 190 lines).
- Ask for JSON only: up to 3 candidates, each with `uuid` and `confidence` (high, medium or low), and an optional `not_in_catalog` free-text guess.
- Never invent uuids: the server drops any candidate not in the list it sent.
- System prompt: South Florida diving, underwater photos. Identify only animals clearly visible; say "none" for an empty reef or a diver shot.

### Server
- `POST /Logbook/photos/{uuid}/identify` and `POST /api/photos/{uuid}/identify`, both owner-only.
- Responses are cached per photo, so asking twice is free.
- Store the suggestion on the photo (`ai_suggestions` JSON) for auditing and tuning.

### Interface
- In the photo viewer: "What's this?" shows 1-3 suggestion chips with confidence, plus the species' own picture to compare against.
- Tap to tag, using the existing tag flow, so the dive gets tagged too.
- A `not_in_catalog` guess offers "Add as your own species".

### Gating
- Early access first (Pablo), like species.

### Privacy
- The photo is sent to Anthropic for analysis. This needs a line in the privacy policy, alongside the HealthKit one.
- The Anthropic API doesn't train on API data by default.

### Effort
- About half a day to a day for web and API, plus a small app button.

### Pablo provides
- An Anthropic Console account and API key, stored as `ANTHROPIC_API_KEY` in the App Service settings.
- A monthly spend limit set in the console.
