# TitaKita — All-in-One Deploy

This is the simplest way to run a self-hosted **TitaKita** node. The all-in-one
image bundles the frontend, backend, web server, queue worker and scheduler into
a single container; Postgres and Redis run as two small companion containers via
`docker-compose.yml`.

> Built on the open-source [Hi.Events](https://hi.events) project (AGPL-3.0).

---

## Option A — Docker Compose (any server, recommended)

Works on any machine with Docker: your laptop, a $5 VPS (Hetzner, DigitalOcean,
Vultr…), or your own hardware.

### 1. Get the code

```bash
git clone <your-titakita-repo-url>
cd <repo>/docker/all-in-one
```

### 2. Create config + secrets (one command)

```bash
./setup.sh
```

This copies `.env.example` → `.env` and auto-generates the two required secrets
(`APP_KEY`, `JWT_SECRET`). No manual key generation needed.

> Windows without WSL/Git-Bash? Generate the two keys manually — see
> **"Generating secrets manually"** below — then run `docker compose up -d`.

### 3. Start TitaKita

```bash
docker compose up -d
```

### 4. Create your account

Open **http://localhost:8123/auth/register**.

That's it. With the default `.env` you get a **"try it out" node** that needs no
external accounts: email is written to the container logs instead of being sent,
and payments are disabled. Perfect for kicking the tyres before going live.

---

## Going to production

Before exposing your node to the public, edit `.env` and set:

| Setting | What to put |
|---|---|
| `VITE_FRONTEND_URL`, `APP_FRONTEND_URL`, `APP_CDN_URL` | Your real `https://your-domain` |
| `VITE_API_URL_CLIENT` | `https://your-domain/api` |
| `MAIL_*` | Your SMTP provider (SES / Postmark / SendGrid / Mailgun) so emails actually send |
| `STRIPE_PUBLIC_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET` | Your Stripe keys, to take payments |
| `FILESYSTEM_PUBLIC_DISK` / `FILESYSTEM_PRIVATE_DISK` | Switch to `s3-public` / `s3-private` + add the `AWS_*` vars if you use object storage (e.g. Cloudflare R2) for images and PDF tickets |

Put a reverse proxy with HTTPS (Caddy, Traefik, or nginx + certbot) in front of
port `8123`, and keep your `APP_KEY` / `JWT_SECRET` stable across restarts (the
generated `.env` already does this — just don't delete it).

See `TitaKita规划` and the upcoming *Deployment Pre-flight Checklist* for the
full account-by-account walkthrough.

---

## Option B — Railway (managed, button-style)

[Railway](https://railway.app) hosts the node for you (no server admin). The repo
ships a `railway.json` that tells Railway to build from `Dockerfile.all-in-one`.

One-time setup (needs a Railway account + this repo on GitHub):

1. Push this repo to GitHub.
2. Railway → **New Project → Deploy from GitHub repo** → pick this repo.
   Railway reads `railway.json` and builds the all-in-one image.
3. In the project, add two plugins: **PostgreSQL** and **Redis**.
4. On the TitaKita service, set environment variables (Variables tab):
   - `APP_KEY` (run `echo base64:$(openssl rand -base64 32)`)
   - `JWT_SECRET` (run `openssl rand -base64 32`)
   - `DATABASE_URL` → reference the Postgres plugin's connection string
   - `REDIS_HOST` / `REDIS_PORT` → reference the Redis plugin
   - `VITE_FRONTEND_URL` / `APP_FRONTEND_URL` → your Railway public URL
   - `VITE_API_URL_CLIENT` → `<public-url>/api`
   - plus `MAIL_*` and `STRIPE_*` when you go live
5. Set the service's target port to **80** if Railway doesn't auto-detect it.

> A true one-click "Deploy on Railway" button requires publishing a Railway
> *template* (which bundles app + Postgres + Redis + env) under your own Railway
> account. That's a guided follow-up step once the repo is on GitHub.

---

## Generating secrets manually

If you can't run `setup.sh`:

**Unix / macOS / WSL / Git-Bash**
```bash
echo base64:$(openssl rand -base64 32)   # -> APP_KEY
openssl rand -base64 32                   # -> JWT_SECRET
```

**Windows PowerShell**
```powershell
"base64:$([Convert]::ToBase64String([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32)))"  # APP_KEY
[Convert]::ToBase64String([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32))              # JWT_SECRET
```

Paste the values into `.env`, then `docker compose up -d`.
