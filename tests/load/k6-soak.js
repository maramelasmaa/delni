/**
 * Soak Test — 20 VUs, 30 minutes
 * Detects memory leaks, connection pool exhaustion, and gradual latency drift.
 * p(95) must remain stable: if it rises over time, there is a leak.
 *
 * k6 run \
 *   -e BASE_URL=https://delni.ly \
 *   -e PROVIDER_SLUG=<slug> \
 *   -e CATEGORY_SLUG=<slug> \
 *   --out json=results/soak.json \
 *   tests/load/k6-soak.js
 *
 * Analyse latency drift:
 *   jq '[.[] | select(.metric=="http_req_duration") | {t:.data.time, v:.data.value}]' \
 *     results/soak.json | ...
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const BASE_URL      = (__ENV.BASE_URL   || 'http://localhost').replace(/\/$/, '');
const PROVIDER_SLUG = __ENV.PROVIDER_SLUG || 'test-provider';
const CATEGORY_SLUG = __ENV.CATEGORY_SLUG || 'test-category';

export const errorRate = new Rate('errors');
export const p95Trend  = new Trend('combined_p95_ms');  // watch for drift over time

export const options = {
  vus:      20,
  duration: '30m',
  thresholds: {
    http_req_failed:                              ['rate<0.01'],
    errors:                                       ['rate<0.01'],
    'http_req_duration{name:home}':               ['p(95)<1000'],
    'http_req_duration{name:search_keyword}':     ['p(95)<1200'],
    'http_req_duration{name:provider_detail}':    ['p(95)<1200'],
    'http_req_duration{name:top_rated}':          ['p(95)<1200'],
    'http_req_duration{name:categories}':         ['p(95)<600'],
  },
};

const HEADERS = { Accept: 'application/json' };

// Arabic search keywords (URL-encoded)
const KEYWORDS = [
  '%D9%83%D9%87%D8%B1%D8%A8%D8%A7%D8%A1',  // كهرباء
  '%D8%B3%D8%A8%D8%A7%D9%83',              // سباك
  '%D8%AA%D9%86%D8%B8%D9%8A%D9%81',       // تنظيف
  '%D8%B7%D8%A8%D8%A7%D8%AE',             // طباخ
  '%D9%86%D8%AC%D8%A7%D8%B1',             // نجار
];

function randomThink(): void {
  sleep(Math.random() * 2 + 1); // 1–3 s think time
}

export default function () {
  const roll = Math.random();
  let r;

  if (roll < 0.30) {
    // 30% — homepage
    r = http.get(`${BASE_URL}/api/v1/home`, { headers: HEADERS, tags: { name: 'home' } });
    check(r, { 'home not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
    p95Trend.add(r.timings.duration);

  } else if (roll < 0.55) {
    // 25% — keyword search (Arabic)
    const kw = KEYWORDS[Math.floor(Math.random() * KEYWORDS.length)];
    r = http.get(
      `${BASE_URL}/api/v1/search?keyword=${kw}&per_page=15&page=${Math.ceil(Math.random() * 3)}`,
      { headers: HEADERS, tags: { name: 'search_keyword' } },
    );
    check(r, { 'search not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
    p95Trend.add(r.timings.duration);

  } else if (roll < 0.75) {
    // 20% — provider detail (heaviest single query)
    r = http.get(
      `${BASE_URL}/api/v1/providers/${PROVIDER_SLUG}`,
      { headers: HEADERS, tags: { name: 'provider_detail' } },
    );
    check(r, { 'provider not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
    p95Trend.add(r.timings.duration);

  } else if (roll < 0.85) {
    // 10% — top-rated
    r = http.get(`${BASE_URL}/api/v1/top-rated`, { headers: HEADERS, tags: { name: 'top_rated' } });
    check(r, { 'top-rated not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
    p95Trend.add(r.timings.duration);

  } else {
    // 15% — browse category
    r = http.get(
      `${BASE_URL}/api/v1/categories/${CATEGORY_SLUG}`,
      { headers: HEADERS, tags: { name: 'categories' } },
    );
    check(r, { 'category not 5xx': (res) => res.status < 500 });
    p95Trend.add(r.timings.duration);
  }

  randomThink();
}
