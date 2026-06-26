// Shared config + helpers for the Delni k6 load tests.
//
// All tuning is driven by env vars so the same scripts run against local,
// staging, and production without edits:
//   BASE_URL   – API origin (default https://delni.ly)
//   API_PREFIX – versioned prefix (default /api/v1)
//   AUTH_TOKEN – optional Sanctum bearer token to exercise authed endpoints
import { check } from 'k6';
import { Counter, Rate } from 'k6/metrics';

export const BASE_URL = (__ENV.BASE_URL || 'https://delni.ly').replace(/\/+$/, '');
export const API_PREFIX = __ENV.API_PREFIX || '/api/v1';
export const AUTH_TOKEN = __ENV.AUTH_TOKEN || '';

export const API = `${BASE_URL}${API_PREFIX}`;

// Track rate-limited responses separately: a 429 from Laravel's throttle
// middleware is expected behaviour under a single-IP test, NOT a backend
// failure. We measure latency on real (2xx) responses and watch 429s as a
// signal that we've saturated the per-IP limiter (see README).
export const rateLimited = new Rate('rate_limited');
export const serverErrors = new Counter('server_errors_5xx');

export function jsonHeaders() {
  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };
  if (AUTH_TOKEN) {
    headers.Authorization = `Bearer ${AUTH_TOKEN}`;
  }
  return { headers };
}

// Standard pass/fail check for a read endpoint. Returns true only for a
// genuine 2xx so the caller can skip data extraction on throttled requests.
export function checkRead(res, name) {
  const is429 = res.status === 429;
  const is5xx = res.status >= 500;

  rateLimited.add(is429);
  if (is5xx) {
    serverErrors.add(1);
  }

  check(res, {
    [`${name}: not a server error`]: () => !is5xx,
    [`${name}: 2xx or throttled (429)`]: () => res.status < 400 || is429,
  });

  return res.status >= 200 && res.status < 300;
}

export function pick(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

// Arabic + latin search terms that exercise the FULLTEXT path and the
// short-token LIKE fallback in ProfileSearchService.
export const SEARCH_TERMS = ['تصميم', 'برمجة', 'تسويق', 'تصوير', 'مصم', 'web', 'design', 'app'];
