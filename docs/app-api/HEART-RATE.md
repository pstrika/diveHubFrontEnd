# Heart rate (web 12.26.0, 2026-10-23)

Wrist heart rate on dive profiles. Today it comes from Garmin Descent `.FIT`
imports (record field 3, every second) and from the apps' own sources
(Apple Watch HealthKit heart-rate samples on iOS; libdivecomputer's
per-sample `heartbeat` on BLE downloads).

## Full dive record (`GET /api/dives/{uuid}`, `/api/dives?ids=`)

- `profile[].heartRate` - integer bpm. Absent on samples with no reading.
  Draw the series only when at least one sample has it.
- `heart_rate_from` - computer model (string) or null. Like `pressure_from`
  / `deco_from`: when the main computer has no heart rate, the server
  copies the reading at the same time into the dive from another computer
  worn on the dive (a Descent on the wrist next to a Petrel), within 30 s.
  The `profile` you receive already has it filled in.
- `sensor_data.heart_rate_avg`, `sensor_data.heart_rate_max` - integers,
  bpm, from the watch's session summary. Scalars, so they also come through
  the summary feed (`/api/dives/changes`).
- `chart_colors.heart_rate` - `#f06292`.

## Upload (`POST /api/dives`)

Send `heartRate` per sample (bpm) in the profile array and, if you have a
summary, `sensor_data.heart_rate_avg` / `heart_rate_max`. A sample's
libdivecomputer-named `heartbeat` is accepted too and renamed to `heartRate`
on the way in (12.26.1); everything else in a sample is stored verbatim.

## Web

Dive page chart: "Heart rate" chip, off by default, own right-hand axis in
bpm, tooltip "Heart rate: 83 bpm". The computer notes line shows
"Heart rate[ from the Descent G1]: avg 83, max 118 bpm".
