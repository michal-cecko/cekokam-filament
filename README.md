# Cekokam Dashboard

Internal CRM, billing and subscriber management for a regional TV/stream operator. Built for Cekokam s.r.o.

## What it does

- **Customers & subscriptions** — household-level customer records, multiple service types (TV packages, internet tiers, equipment rentals), seasonal price changes
- **Billing** — automatic invoice generation from active subscriptions, supports multiple payment methods
- **Payment matching** — IMAP inbox watcher polls Tatra banka payment notifications and auto-matches against open invoices by amount + IBAN
- **SMS notifications** — BulkGate integration for payment reminders and service updates
- **Channel/stream management** — companion to [`cekokam-stream-server`](https://github.com/michal-cecko/cekokam-stream-server) (the Go service that serves the HLS streams)
- **Multi-role admin** — admin, agent, technician views in a single Filament panel

## Stack

| Layer | Tech |
|---|---|
| Backend | **Laravel 12** on PHP 8.5, **Octane** + RoadRunner |
| Admin | **Filament v5** |
| SMS | BulkGate PHP SDK |
| Email parsing | IMAP for payment auto-matching |
| Errors | Sentry |
| Tests | PHPUnit + ParaTest |
| Code style | Laravel Pint |
| Deploy | Docker → Dokploy |

## Local dev

```bash
cp .env.example .env
vendor/bin/sail up -d
vendor/bin/sail composer install
vendor/bin/sail npm install
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed
vendor/bin/sail npm run dev
```

Admin at `/admin`.

## Required env

| Var | Purpose |
|---|---|
| `COMPOSER_AUTH` | Filament Pro auth (build time, JSON) |
| `IMAP_USERNAME` / `IMAP_PASSWORD` / `IMAP_HOST` | Payment-notification mailbox |
| `BULKGATE_APP_TOKEN` / `BULKGATE_APP_ID` | SMS sender |
| `STREAM_SERVER_TOKEN` | Bearer token for cekokam-stream-server API |
| `ADMIN_SEED_PASSWORD` | Initial seeder admin password (random if unset) |

## CI

GitHub Actions `ci.yml`:

1. **test** — Pint + PHPUnit against Postgres service
2. **deploy-worker** — re-deploy worker via Dokploy API
3. **notify** — Telegram on failure

## Deploy

Two-stage `Dockerfile`. Build stage uses a pre-built base image (deploy the "base" Dokploy service first). Runtime is lean PHP 8.4 alpine + Octane.

## License

[MIT](LICENSE) © Michal Čečko
