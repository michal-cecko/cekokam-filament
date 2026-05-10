# Stream pipeline split — Laravel → Go cutover

The HLS download/serve pipeline has moved from this Laravel app to a sibling Go service at `../cekokam-stream-server`. Postgres and Filament admin stay here. The Go server polls this dashboard for the channel list and logos via a small bearer-token-protected internal API and writes m3u8/.ts files to a shared volume.

## What lives where now

| Concern | Owner |
|---|---|
| Filament admin, ChannelStream CRUD | Laravel (this repo) |
| Postgres | Laravel |
| Channel list + logo bytes (internal API) | Laravel |
| Upstream m3u8 polling, .ts download, validation | Go |
| m3u8 rewrite + atomic publish | Go |
| File serving (`/streams/...`, `/logos/...`) | Go |
| Sequence-folder pruning (last 50) | Go |

## Cutover order (shared-volume deployment)

The Go server runs on the same VPS as Laravel and mounts the existing public-storage volume. This means old PHP-generated files and new Go-generated files live side by side; cutover is instant once the cron is disabled.

### 1. Deploy the Go server (idle)

- Mount Laravel's `storage/app/public` into the Go container at `/storage`.
- Set env:
  - `STORAGE_DIR=/storage`
  - `DASHBOARD_URL=https://<this-app>`
  - `DASHBOARD_TOKEN=<value of STREAM_SERVER_TOKEN below>`
  - `PUBLIC_URL=https://<go-server-public-base>` (must match Laravel's `STREAM_SERVER_PUBLIC_URL`)
  - `LISTEN_ADDR=:8080`
- The dashboard endpoint is not yet live, so the Go server will retry on its 60s sync loop and log 401/404 — harmless.

### 2. Deploy this Laravel PR

- Set in Laravel `.env`:
  - `STREAM_SERVER_PUBLIC_URL=https://<go-server-public-base>`
  - `STREAM_SERVER_TOKEN=<long random string>`
- The internal API now responds at:
  - `GET /api/internal/channels`
  - `GET /api/internal/channels/{slug}/logo`
- Go's next sync tick picks up the channel list and starts producing fresh m3u8s on the shared volume. PHP cron is still running at this point — both writers coexist; segments and m3u8 will get rewritten by whichever ticks last. This is fine for a few minutes.

### 3. Disable the host crontab entries

The PHP cron lines are managed outside this repo (`crontab -e` on the VPS). Find and remove every line matching:

```
* * * * * sleep N && docker exec ... app:download-stream-files <slug>
* * * * * sleep N && docker exec ... app:prune-downloaded-streams
```

There are roughly 24 staggered `app:download-stream-files` lines plus the prune line. After this step Laravel stops writing to `storage/app/public/streams/`; Go is the sole writer.

### 4. Verify Go output

```bash
curl -i ${STREAM_SERVER_PUBLIC_URL}/streams/<slug>/stream.m3u8
curl -I ${STREAM_SERVER_PUBLIC_URL}/streams/<slug>/ts/<seq>/<hash>.ts
curl -I ${STREAM_SERVER_PUBLIC_URL}/logos/<slug>.png
curl ${STREAM_SERVER_PUBLIC_URL}/healthz
```

Open the m3u8 in VLC or `ffplay` and confirm playback. Watch the Go server's JSON logs for `segments downloaded` lines.

### 5. Repoint downstream consumers

Anything embedding the old Laravel `/storage/streams/*` URL must be updated to point at `STREAM_SERVER_PUBLIC_URL`. The Filament admin's `stream_url` column reads from `ChannelStream::getStreamUrlAttribute()`, which now returns the Go-server URL — copy from there.

### 6. Cleanup notes

- **Channel deletion no longer cleans disk.** The `static::deleted` boot hook on `ChannelStream` was removed (Go owns the files). After deleting a channel via Filament, its `streams/<slug>/` directory survives on the shared volume; remove by hand if disk usage matters. (A future webhook from Laravel to Go could automate this.)
- **Pre-existing logo filename inconsistency.** Filament's `FileUpload` saves logos with `preserveFilenames()` (e.g. `logos/foo-channel.png`), while the model accessor and Go server expect `logos/{slug}.png`. The internal API resolves this: it serves bytes from `$model->logo` (the actual stored path), and Go saves them locally as `{slug}.png`. Existing oddly-named logo files on disk are harmless — orphaned but small. Cleanup is optional.
- **Orphan sequence folders for already-deleted channels.** The Go pruner only touches directories belonging to currently-active channels. Anything left over from past channel deletions can be removed by hand.
