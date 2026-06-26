# Delni — Backend Load Testing

k6 scripts and runbook for load testing the deployed public API (`https://delni.ly/api/v1`).

## TL;DR

```bash
# 1. Install k6 (https://grafana.com/docs/k6/latest/set-up/install-k6/)
winget install k6 --source winget      # Windows
# brew install k6                       # macOS

# 2. Smoke test — prove the deployment is healthy (do this first)
k6 run -e BASE_URL=https://delni.ly load-testing/smoke.js

# 3. Realistic browse load
k6 run -e BASE_URL=https://delni.ly load-testing/browse.js

# 4. Push harder
k6 run -e BASE_URL=https://delni.ly -e PEAK_RPS=200 load-testing/browse.js
```

| Script | Purpose |
| --- | --- |
| `smoke.js` | 1 VU hits every public endpoint once; asserts 2xx. Gate before load. |
| `browse.js` | Weighted real-world read mix (home → search/top-rated/category → provider detail), arrival-rate controlled. |
| `lib/helpers.js` | Shared config (env-driven), 429/5xx tracking, data helpers. |

Env vars: `BASE_URL`, `API_PREFIX` (default `/api/v1`), `PEAK_RPS` (default 50), `AUTH_TOKEN` (optional Sanctum bearer for authed paths).

---

## ⚠️ Two things WILL distort your results — read before running

### 1. Rate limiters throttle by IP — a single load box hits 429s fast

The API is throttled per-IP in `AppServiceProvider::configureRateLimiters()`:

| Endpoint | Limit (guest) |
| --- | --- |
| `/home` | 60 / min / IP |
| `/search` | 20 / min / IP |
| `/top-rated` | 30 / min / IP |
| `/providers/{slug}` | 60 / min / IP |
| `/search/suggestions` | 60 / min / IP |

`TRUSTED_PROXIES=127.0.0.1`, so you **cannot** spoof `X-Forwarded-For` from an external box (good security — bad for single-IP load gen). From one machine you'll saturate these limits within seconds and mostly measure the throttler, not the backend.

Pick one:

- **(Recommended) Distributed run** — use [Grafana Cloud k6](https://grafana.com/docs/k6/latest/results-output/real-time/cloud/) (`k6 cloud browse.js`). Load comes from many IPs, so per-IP limits behave as they would for real users.
- **Temporarily relax limits for the test window** — raise the `Limit::perMinute(...)` values (or gate them behind an env flag) in `AppServiceProvider`, redeploy, test, then revert. Cleanest if you want true single-box capacity numbers.
- **Stay under the limits** — keep `PEAK_RPS` low enough that per-IP rates aren't exceeded. Only useful for a latency baseline, not a saturation test.

`browse.js` already treats 429 as an expected, tracked metric (`rate_limited`) rather than a failure — so a run still produces clean latency numbers for the 2xx traffic. Watch the `rate_limited` value: if it's high, you're testing the throttler.

### 2. `CACHE_STORE=database` makes MySQL the cache — and a bottleneck

From `.env.example` the production defaults are:

```
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

Every `Cache::flexible()` call in `PublicFrontendService` / `HomeController` (homepage stats, profile counts, city counts) then does a **read + occasional write to the `cache` MySQL table**, competing with the real query workload. Under load this both (a) inflates DB load and (b) makes results non-representative of a properly-tuned prod box.

**Before a serious load test, switch the deployed app to Redis** (already configured — `REDIS_*` vars + `phpredis` client are present, you just need a Redis container in Coolify):

```
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis     # optional; only affects Filament admin, the mobile API is stateless (Sanctum tokens)
```

This is a **deployment/env change you make in Coolify** — not a code change.

---

## Pre-flight checklist (run on the deployed box)

```bash
php artisan config:cache    # config cached
php artisan route:cache     # routes cached
php artisan event:cache     # events cached
# Confirm APP_DEBUG=false  → PublicFrontendService::inspectQueries() skips the query log.
#   With APP_DEBUG=true every request logs every SQL statement in memory = artificially slow + memory growth.
```

- [ ] `APP_DEBUG=false` (critical — see above)
- [ ] OPcache enabled in the PHP image (`opcache.enable=1`)
- [ ] Seeded, realistic data — at minimum dozens of discoverable providers across several categories/cities. `smoke.js` warns if `/search` returns nothing.
- [ ] PHP-FPM `pm.max_children` sized vs. container CPU/RAM, and MySQL `max_connections` ≥ (FPM workers + queue workers + headroom).
- [ ] Redis in place if you applied the cache change above.

## What to watch while testing

- **k6 side:** `http_req_duration` p95/p99 (threshold p95<500ms on 2xx), `http_req_failed`, `rate_limited`, `server_errors_5xx`, `http_reqs` (achieved RPS), `vus`.
- **Server side:** MySQL `SHOW PROCESSLIST` / slow query log, CPU + RAM per container, PHP-FPM active workers, Redis ops/sec (if enabled). Watch for connection-pool exhaustion (`Too many connections`) — the usual first wall.

## Hot paths, in cost order (where to expect the wall)

1. `GET /search` — FULLTEXT `MATCH AGAINST` + users/profile_stats joins + ranking + pagination. Heaviest, and most-called. Backed by `profiles_search_fulltext`, `profiles_is_complete_*` composite indexes, `profile_stats_rating_reviews_index`.
2. `GET /providers/{slug}` — loads ~10 relations (user, stats, city, category, subcategories, links, credentials, portfolio+images, approved reviews+users). Many queries per request, but all indexed and eager-loaded (no N+1; `preventLazyLoading` is on outside prod).
3. `GET /home` & `GET /top-rated` — multiple aggregate counts, but the counts/stats are wrapped in `Cache::flexible` (stale-while-revalidate). First request after a cache miss is the slow one; the rest serve from cache.

The query layer is already indexed and N+1-safe. If you find a wall, it will almost certainly be **(a) the per-IP rate limiter, (b) the database cache store, (c) MySQL connection limits, or (d) PHP-FPM worker count** — in that order — not the SQL.
