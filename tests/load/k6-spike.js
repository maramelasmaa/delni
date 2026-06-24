/**
 * Spike Test — 0 → 200 VUs in 10 s, hold 30 s, ramp down in 10 s
 * Simulates a sudden surge (viral share, flash sale, etc.).
 * Watch for 502 (PHP-FPM pm.max_children exhausted) and queue backup.
 *
 * k6 run \
 *   -e BASE_URL=https://delni.ly \
 *   --out json=results/spike.json \
 *   tests/load/k6-spike.js
 */
import http from 'k6/http';
import { check } from 'k6';
import { Rate } from 'k6/metrics';

const BASE_URL = (__ENV.BASE_URL || 'http://localhost').replace(/\/$/, '');

export const errorRate = new Rate('errors');

export const options = {
  stages: [
    { duration: '10s', target: 200 }, // instant spike
    { duration: '30s', target: 200 }, // hold
    { duration: '10s', target:   0 }, // ramp down
  ],
  thresholds: {
    http_req_failed: ['rate<0.10'],
    errors:          ['rate<0.10'],
  },
};

const HEADERS = { Accept: 'application/json' };

export default function () {
  // 60% home / 40% top-rated — the two heaviest uncached public endpoints
  const roll = Math.random();

  if (roll < 0.6) {
    const r = http.get(`${BASE_URL}/api/home`, { headers: HEADERS, tags: { name: 'home' } });
    check(r, { 'home not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
  } else {
    const r = http.get(`${BASE_URL}/api/top-rated`, { headers: HEADERS, tags: { name: 'top_rated' } });
    check(r, { 'top-rated not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
  }
  // No sleep — maximise pressure on the server
}
