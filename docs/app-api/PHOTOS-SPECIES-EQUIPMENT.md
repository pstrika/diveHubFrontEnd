# Photos, marine life and equipment: app API

Web 12.17.0, 2026-10-17. All routes use the same Sanctum bearer token, logbook gate and `throttle:logbook-sync` limiter as `/api/dives`. JSON in and out (`Accept: application/json`), except the uploads, which are multipart.

## Feature flags

`GET /api/me/features`

```json
{"photos": true, "max_photos_per_dive": 20, "species": true, "equipment": true, "share_sightings": null}
```

- `species` and `equipment` are **early access**: today only pstrika@gmail.com has them. For other accounts every species or equipment route returns **404**, and the dive JSON carries `species: null` and `equipment: null`. Hide those screens when the flag is false.
- `share_sightings`: `null` means the diver was never asked. Ask once, before their first species tag:
  - **Question:** "Share your sightings? Other divers see the species, the site and the date. Your dives, photos and notes stay private."
  - **Answer:** send it with `POST /api/me/share-sightings {"share": true|false}`.

## Dive JSON (GET /api/dives/{uuid}, GET /api/dives?uuids=)

New keys:

- `photos`: an array of photo objects (see below), in display order.
- `species`: `[{uuid, count|null}]`, or `null` when the feature is off.
- `equipment`: `[{uuid, auto}]`, or `null` when the feature is off.
  - `auto: true` is a dive computer whose serial number matches this dive's computer (or an attached computer's). The server adds these; don't send them back.

Any change to a dive's photos, species or gear, from the web or the app, logs `updated` for that dive in `/api/dives/changes`, so the existing refetch path picks it up.

## Photos (every account)

Each dive takes up to 20 photos. They're private to the diver.

| Method | Path | Body | Returns |
|---|---|---|---|
| GET | `/api/dives/{dive}/photos` | | `{photos: [...]}` |
| POST | `/api/dives/{dive}/photos` | multipart: `photo` (jpg/png/webp, 20 MB max), `caption?`, `uuid?` | 201 `{photo}` |
| PATCH | `/api/photos/{uuid}` | `{caption?, position?}` | `{photo}` |
| DELETE | `/api/photos/{uuid}` | | 204 (also removes its site-page copy) |
| POST | `/api/photos/{uuid}/share` | `{share: true|false}` | `{photo}` |
| PUT | `/api/photos/{uuid}/species` | `{species: [uuid]}`, the whole list | `{photo, dive_species}` |
| GET | `/api/photos/{uuid}/web` or `/thumb` | | image/webp (1600 px / 480 px) |

The photo object:

```json
{
  "uuid": "...", "dive_uuid": "...",
  "url": "https://divers-hub.com/api/photos/{uuid}/web",
  "thumb_url": ".../thumb",
  "width": 1600, "height": 1067,
  "caption": null, "taken_at": "2026-10-03T08:47:00+00:00", "position": 1,
  "shared": null, "species": ["uuid"], "updated_at": "..."
}
```

- **Image URLs** need the `Authorization` header.
- **Upload one photo per request.** Phone photos run 5-15 MB and the server's request limit is about 25 MB.
- **Convert HEIC to JPEG** before uploading.
- **Retries:** send your own `uuid` with each upload. Re-sending the same `uuid` returns the existing photo instead of making a duplicate.
- **Upload limit:** once a dive has 20 photos, further uploads get 422 with `errors.photo`.
- **`taken_at`** is read from the file's EXIF before the original is discarded. Keep EXIF in the JPEG.
- **`shared`** is `null`, `pending`, `approved` or `rejected`.
  - Sharing puts a copy on the dive site's public page through the normal admin photo review.
  - It's only possible when the dive has a catalog `site_id`; otherwise it returns 422.
  - `share: false` takes the copy down.
- **Photo species:** putting a species on a photo also adds it to the dive.

## Marine life (early access)

| Method | Path | Body | Returns |
|---|---|---|---|
| GET | `/api/species?updated_since=ISO` | | `{categories, species: [...], server_time}` |
| POST | `/api/species` | `{common_name, category, kingdom?, phylum?, class?, subclass?, division?, order?, family?, genus?, scientific_name?, description?, other_names?}` | 201 `{species}` (the diver's own, private) |
| PATCH | `/api/species/{uuid}` | same fields (own species only) | `{species}` |
| DELETE | `/api/species/{uuid}` | | 204 (removed from every dive and photo) |
| PUT | `/api/dives/{dive}/species` | `{species: [{uuid, count?}]}`, the whole list | `{species: [{uuid, count}]}` |

- **The catalog:** 182 common South Florida species, plus the diver's own (`custom: true`).
- **Sync:** cache it, and refresh with `updated_since=<server_time from the last call>`. Deleted custom species aren't reported; do a full refetch now and then.
- **Category slugs:**
  - Fish and sharks: `sharks`, `rays`, `reef_fish`, `pelagic_fish`, `eels`
  - Other animals: `turtles`, `dolphins_manatees`, `crustaceans`, `octopus_squid`, `sea_slugs_mollusks`, `urchins_stars`, `jellyfish`
  - Corals, sponges and the rest: `corals`, `sponges`, `other`
- **Species object:**

  ```json
  {"uuid", "common_name", "other_names": [], "category",
   "kingdom", "phylum", "class", "subclass", "division", "order", "family", "genus",
   "scientific_name", "description", "photo_url", "custom", "updated_at"}
  ```

  Ranks follow the Wikipedia taxobox, so many are `null`. `photo_url` is `null` for now.
- **Removing a species from the dive's list** also removes it from that dive's photos.

## Equipment (early access)

| Method | Path | Body | Returns |
|---|---|---|---|
| GET | `/api/equipment` | | `{categories, items, configs, suggested_computers}` |
| POST | `/api/equipment/items` | multipart or JSON: `{category, name, manufacturer?, model?, serial_number?, purchased_on?, last_service_on?, notes?, photo?}` | 201 `{item}` |
| POST or PATCH | `/api/equipment/items/{uuid}` | same fields plus `retired?` (POST when sending a photo) | `{item}` |
| DELETE | `/api/equipment/items/{uuid}` | | 204 |
| GET | `/api/equipment/items/{uuid}/photo` | | image/webp |
| POST | `/api/equipment/configs` | `{name, notes?, items: [uuid]}` | 201 `{config}` |
| PATCH | `/api/equipment/configs/{uuid}` | same | `{config}` |
| DELETE | `/api/equipment/configs/{uuid}` | | 204 (dives keep their gear) |
| PUT | `/api/dives/{dive}/equipment` | `{items: [uuid], config?: uuid}`, the whole list | `{equipment: [...]}` |

- **Categories:** `regulator`, `computer`, `bcd`, `tank`, `mask`, `fins`, `camera`, `other`.
- **Dates** are `YYYY-MM-DD`.
- **Configurations** are named sets of items. Applying one copies its items onto the dive, so editing the configuration later doesn't change past dives.
- **`suggested_computers`** lists the computers the diver's dives came from that aren't in their gear yet: `[{manufacturer, model, serial, dives}]`. Create one as a `computer` item with that `serial_number`, and its dives link automatically.
