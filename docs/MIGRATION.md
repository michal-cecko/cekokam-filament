# Stream pipeline split — Laravel → Go cutover

The HLS download/serve pipeline has moved from this Laravel app to a sibling Go service at `../cekokam-stream-server`. Postgres and Filament admin stay here. The Go server polls this dashboard for the channel list and logos via a small bearer-token-protected internal API and writes m3u8/.ts files to its storage volume.

## Production deployment topology

| Component | Where | URL |
|---|---|---|
| Filament admin (this repo) | Dokploy on the main VPS | `https://admin.cekokam.sk` |
| Postgres | Dokploy-managed on the main VPS | service `cekokam-postgres` on the `database` network |
| Redis | Compose service alongside this app | service `redis` on the `database` network |
| Go stream server | Separate VPS | `http://37.46.211.68` |

> **Mixed-content caveat**: the dashboard is HTTPS (`admin.cekokam.sk`) but the stream server is plain HTTP (`http://37.46.211.68`). The Go server embeds `http://37.46.211.68/logos/<slug>.png` into the rewritten m3u8 (via `tvg-logo`). Native HLS players (VLC, ffplay, iOS/Android, set-top boxes) ignore mixed-content rules and work fine. Browser-based players (hls.js loaded inside an HTTPS page) will block these as mixed-content. If browser playback inside the dashboard becomes a requirement, give the stream server a TLS-enabled subdomain (e.g. `stream.cekokam.sk`) and update `STREAM_SERVER_PUBLIC_URL` accordingly.

## What lives where now

| Concern | Owner |
|---|---|
| Filament admin, ChannelStream CRUD | Laravel (this repo) |
| Postgres | Laravel-side / Dokploy |
| Channel list + logo bytes (internal API) | Laravel |
| Upstream m3u8 polling, .ts download, validation | Go |
| m3u8 rewrite + atomic publish | Go |
| File serving (`/streams/...`, `/logos/...`) | Go |
| Sequence-folder pruning (last 50) | Go |

## Cutover order

### 1. Deploy the Go server (idle)

On the stream VPS at `37.46.211.68`:

- `STORAGE_DIR=/storage` (mounted from the existing Laravel public-storage path on that VPS, or fresh)
- `DASHBOARD_URL=https://admin.cekokam.sk`
- `DASHBOARD_TOKEN=<value of STREAM_SERVER_TOKEN below>`
- `PUBLIC_URL=http://37.46.211.68` — must match Laravel's `STREAM_SERVER_PUBLIC_URL`
- `LISTEN_ADDR=:8080` (or `:80` if running directly without a reverse proxy)

The dashboard endpoint is not yet live, so the Go server retries on its 60s sync loop and logs 401s — harmless.

### 2. Deploy Laravel (this repo) to Dokploy

In the Dokploy app for this repo, set env (or commit to `.env.prod` — the URLs are already there; just fill `STREAM_SERVER_TOKEN`):

```
STREAM_SERVER_PUBLIC_URL=http://37.46.211.68
STREAM_SERVER_TOKEN=<long random string, must equal Go server's DASHBOARD_TOKEN>
```

The internal API responds at:

- `GET https://admin.cekokam.sk/api/internal/channels`
- `GET https://admin.cekokam.sk/api/internal/channels/{slug}/logo`

Go's next sync tick picks up the channel list and starts producing m3u8s.

### 3. Disable the host crontab on the stream VPS

The PHP cron lines on the stream VPS run `app:download-stream-files <slug>` and `app:prune-downloaded-streams` against the old PHP app. Find and remove every crontab line matching:

```
* * * * * sleep N && docker exec ... app:download-stream-files <slug>
* * * * * sleep N && docker exec ... app:prune-downloaded-streams
```

Roughly 24 staggered download lines plus the prune line. After this step, the Go server is the sole writer.

### 4. Verify Go output

```bash
curl -i http://37.46.211.68/streams/<slug>/stream.m3u8
curl -I http://37.46.211.68/streams/<slug>/ts/<seq>/<hash>.ts
curl -I http://37.46.211.68/logos/<slug>.png
curl http://37.46.211.68/healthz
```

Open the m3u8 in VLC or `ffplay`. Watch the Go server's JSON logs for `segments downloaded`.

### 5. Repoint downstream consumers

Anything embedding old `/storage/streams/*` paths must point at `http://37.46.211.68`. The Filament admin's `stream_url` column reads from `ChannelStream::getStreamUrlAttribute()`, which now returns the Go-server URL — copy from there.

### 6. Cleanup notes

- **Channel deletion no longer cleans disk.** The `static::deleted` boot hook on `ChannelStream` was removed (Go owns the files). After deleting a channel via Filament, its `streams/<slug>/` directory survives on the stream VPS; remove by hand if disk usage matters.
- **Pre-existing logo filename inconsistency.** Filament's `FileUpload` uses `preserveFilenames()` (e.g. `logos/foo-channel.png`), while accessors and Go expect `logos/<slug>.png`. The internal API serves bytes from `$model->logo` (actual stored path); Go saves them locally as `<slug>.png`. Old odd-named logo files on the dashboard's storage are harmless orphans.
- **Orphan sequence folders for already-deleted channels.** Go's pruner only touches active-channel directories. Anything left over from past deletions can be removed by hand.
