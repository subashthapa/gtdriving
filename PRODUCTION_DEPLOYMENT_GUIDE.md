# Production Deployment and Operations Guide

This file records the GT Driving launch and is the reusable playbook for Laravel/Inertia/Vue projects hosted like SG Homes and AcesHost.

## Current GT Driving Setup

- Repository: `https://github.com/subashthapa/gtdriving.git`, branch `main`
- Server: `root@170.64.174.227`
- Directory: `/opt/gtdriving`
- Domains: `gtdriving.com.au` and `www.gtdriving.com.au`
- Compose project/service: `gtdriving-production` / `gtdriving`
- Container/image: `gtdriving-production-gtdriving-1` / `gtdriving:production`
- Reverse proxy: shared `coolify-proxy` Traefik container
- Public network: external Docker network `coolify`
- Database: SQLite in a dedicated named volume
- Storage: dedicated named volume
- TLS: Let's Encrypt using Traefik resolver `letsencrypt`
- Health endpoint: `/up`

Never restart the shared proxy or touch another application's containers during a GT Driving deployment.

## Repository Deployment Files

- `Dockerfile`: multi-stage Composer, Vite and Apache production image.
- `compose.production.yaml`: isolated service, volumes, networks and Traefik labels.
- `.dockerignore`: excludes secrets, local databases, tests and generated dependencies.
- `deploy/.env.example`: production environment template.
- `deploy/docker/entrypoint.sh`: prepares writable directories, runs migrations and optimizes Laravel.
- `deploy/docker/php.ini`: production PHP configuration.
- `bootstrap/app.php`: trusts Traefik's forwarded HTTPS headers.
- `tests/Feature/TrustedProxyTest.php`: verifies HTTPS asset generation behind Traefik.

Commit `composer.lock` and `package-lock.json`. Never commit `.env`, `deploy/.env`, keys, credentials, databases, `vendor`, `node_modules` or `public/build`.

## Server Prerequisites

- Docker Engine and Compose
- Running Traefik with ports 80 and 443
- External `coolify` network connected to Traefik
- ACME HTTP challenge resolver named `letsencrypt`
- Git access to the repository

```bash
ssh root@SERVER_IP
docker ps
docker network inspect coolify
docker inspect coolify-proxy --format '{{json .Config.Cmd}}'
```

Traefik must include `letsencrypt.acme.httpchallenge`, HTTP as its challenge entrypoint, and persistent `acme.json` storage.

## Pre-deployment Checks

```bash
composer install
npm ci
npm run build
php artisan test
npm audit --omit=dev
```

Requirements:

- Tests and clean Vite build pass.
- Production audit has no unresolved vulnerabilities.
- `APP_DEBUG=false`.
- `/up` returns 200.
- Production seeders create only reference data, not demo users/bookings.

At the latest GT Driving deployment, 50 tests and 159 assertions passed; 7 tests were intentionally skipped for disabled Jetstream features. The production npm audit reported zero vulnerabilities.

## Production Environment

```bash
cd /opt/PROJECT
cp deploy/.env.example deploy/.env
chmod 600 deploy/.env
php artisan key:generate --show
```

Put the generated key in the server-only file:

```dotenv
APP_NAME="Application Name"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_TIMEZONE=Australia/Melbourne
APP_URL=https://example.com
APP_DOMAIN=example.com
APP_WWW_DOMAIN=www.example.com
LOG_CHANNEL=stderr
LOG_LEVEL=warning
DB_CONNECTION=sqlite
DB_DATABASE=/var/lib/gtdriving/database.sqlite
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

GT Driving is configured to use Resend SMTP for password resets, email verification, and instructor invitations. Set `MAIL_MAILER=resend_smtp`, a valid `RESEND_API_KEY`, and a sender address on a verified Resend domain before deploying. Test delivery after every mail configuration change.

## First Deployment

```bash
git clone REPOSITORY_URL /opt/PROJECT
cd /opt/PROJECT
cp deploy/.env.example deploy/.env
# Edit deploy/.env and set chmod 600.
docker compose --env-file deploy/.env -f compose.production.yaml build SERVICE
docker compose --env-file deploy/.env -f compose.production.yaml up -d SERVICE
docker inspect CONTAINER --format '{{.State.Health.Status}}'
```

GT Driving:

```bash
cd /opt/gtdriving
docker compose --env-file deploy/.env -f compose.production.yaml build gtdriving
docker compose --env-file deploy/.env -f compose.production.yaml up -d gtdriving
```

The entrypoint runs `migrate --force` and `optimize`. Named database and storage volumes survive rebuilds and container replacement.

For a fresh database, ensure no old project volumes exist before first start, then run only safe reference seeders. GT Driving was verified with 0 users, 4 roles, 24 time slots and 6 packages. Never run `migrate:fresh` or delete production volumes without an approved, tested backup.

## DNS and TLS

Create both records:

```text
A  @    SERVER_IP
A  www  SERVER_IP
```

GT Driving points both names to `170.64.174.227`. Verify authoritative and public DNS:

```bash
dig +short NS example.com
dig +short A example.com @AUTHORITATIVE_NS
dig +short A www.example.com @AUTHORITATIVE_NS
dig +short A www.example.com @1.1.1.1
dig +short A www.example.com @8.8.8.8
dig +short A www.example.com @9.9.9.9
```

Old results remain cached until the previous TTL expires. GT Driving's TTL was 14,400 seconds, approximately four hours.

Use unique Traefik router, middleware and service names for every project. Create HTTP apex/www routers that redirect to HTTPS and HTTPS apex/www routers with:

```yaml
- traefik.enable=true
- traefik.docker.network=coolify
- traefik.http.routers.PROJECT-https.tls=true
- traefik.http.routers.PROJECT-https.tls.certresolver=letsencrypt
- traefik.http.services.PROJECT.loadbalancer.server.port=80
```

Inspect loaded labels and certificates:

```bash
docker inspect CONTAINER --format '{{json .Config.Labels}}'
echo | openssl s_client -connect SERVER_IP:443 -servername example.com 2>/dev/null | openssl x509 -noout -subject -issuer -dates
curl --resolve example.com:443:SERVER_IP https://example.com/up
```

### GT Driving certificate incident

The first `www` ACME request resolved to the old address `103.42.108.46`, so Traefik served `TRAEFIK DEFAULT CERT`. Logs exposed the failed challenge:

```bash
docker logs --since 2h coolify-proxy 2>&1 | grep -E 'DOMAIN|certificate'
```

After correcting authoritative DNS, only GT Driving was recreated to retrigger ACME:

```bash
cd /opt/gtdriving
docker compose --env-file deploy/.env -f compose.production.yaml up -d --force-recreate gtdriving
```

Do not restart the shared proxy for one site's certificate.

## Reverse Proxy and Mixed Content

Traefik terminates HTTPS and sends HTTP to Apache inside Docker. Without trusted proxy headers, Laravel generates `http://` Vite assets and Inertia prefetch URLs, which Chrome blocks as mixed content.

Laravel 11 fix in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

Using `*` is appropriate only when the app is reachable through the controlled proxy network. Restrict proxy ranges if its port is publicly exposed. The regression test sends `X-Forwarded-Host`, `X-Forwarded-Port: 443` and `X-Forwarded-Proto: https` and checks that asset URLs are HTTPS.

Live verification after commit `fd77aff` found 11 secure and 0 insecure asset URLs on each hostname. If Chrome retains an old console error, hard-refresh or clear site data after verifying the server response.

## Routine Deployment

```bash
# Local
php artisan test
npm run build
git add CHANGED_FILES
git commit -m "Describe change"
git push origin main

# Server
ssh root@SERVER_IP
cd /opt/PROJECT
git pull --ff-only origin main
docker compose --env-file deploy/.env -f compose.production.yaml build SERVICE
docker compose --env-file deploy/.env -f compose.production.yaml up -d --no-deps SERVICE
docker inspect CONTAINER --format '{{.State.Health.Status}}'
```

GT Driving uses `/opt/gtdriving`, service `gtdriving`, and container `gtdriving-production-gtdriving-1`. `--no-deps` and unique project naming prevent unrelated services from being recreated.

## Post-deployment Verification

```bash
curl -I https://example.com
curl -fsS https://example.com/up
curl -fsS https://www.example.com/up
docker logs --tail 100 CONTAINER
```

Verify HTML and preload headers contain no `http://example.com/build/assets/` references. Then check SG Homes, AcesHost and every other shared-server site still returns 200.

Browser smoke test:

- Home page and static assets
- Registration, login and logout without 419 errors
- New and existing learner bookings
- Calendar date and available time-slot loading
- Booking cancellation rules
- Instructor learners, history, earnings and future calendar
- Cash payment status workflow
- No JavaScript or mixed-content console errors

## Backups and Rollback

Back up before risky changes or migrations:

```bash
mkdir -p /opt/backups/PROJECT
docker cp CONTAINER:/var/lib/gtdriving/database.sqlite /opt/backups/PROJECT/database-$(date +%Y%m%d-%H%M%S).sqlite
docker cp CONTAINER:/var/www/html/storage/app/public /opt/backups/PROJECT/public-files-$(date +%Y%m%d-%H%M%S)
```

For a consistent active SQLite backup, use SQLite's backup facility or maintenance mode. Test restoration periodically.

Rollback code without deleting volumes:

```bash
cd /opt/PROJECT
git log --oneline -10
git switch --detach KNOWN_GOOD_COMMIT
docker compose --env-file deploy/.env -f compose.production.yaml build SERVICE
docker compose --env-file deploy/.env -f compose.production.yaml up -d --no-deps SERVICE
```

Then revert the bad commit on `main`; do not rewrite shared history. Code rollback does not reverse database migrations, so database recovery needs an explicit migration-aware plan and backup.

## Shared-server Safety Rules

- Give every app unique Compose, service, image, router, middleware, volume and private-network names.
- Share only the external `coolify` ingress network.
- Do not publish app ports directly when Traefik provides ingress.
- Never run Compose commands from the wrong project directory.
- Never run `docker system prune --volumes` on a shared production host.
- Never delete named volumes during a normal deployment.
- Never restart `coolify-proxy` without an approved all-sites maintenance window.
- Use `git pull --ff-only` to reject unexpected server divergence.
- Verify all hosted sites after every deployment.
- Keep secrets only in the server-side environment file with restrictive permissions.

## Recorded GT Driving History

- `6fce13c`: hardened booking flow for production.
- `d8fb9c8`: added shared-server deployment package.
- `c86e21e`: tracked production dependency lockfiles.
- `fd77aff`: trusted proxy HTTPS headers and added regression test.
- Earlier login/logout 419 issues were addressed with session/Inertia fixes and authentication tests.
- The PHP development server's `file_put_contents(): Broken pipe` notice came from Laravel's dev-server wrapper, not a changed application file. Production uses Apache and does not rely on that wrapper.

## Launch Checklist

- Tests, frontend build and production dependency audit pass.
- Production environment is server-only; debug is disabled.
- Sessions are secure and Laravel trusts only the intended proxy path.
- Database/storage have dedicated persistent volumes and tested backups.
- Fresh database has no demo accounts or bookings.
- Apex and `www` resolve to the server on public resolvers.
- Both certificates are valid; HTTP redirects to HTTPS.
- Both health endpoints return 200 and assets use HTTPS.
- Container is healthy and all other hosted sites still return 200.
- Real mail delivery is configured before email features are advertised.
- First administrator is created and assigned securely after launch.
