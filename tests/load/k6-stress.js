/**
 * Stress Test — ramp to 100 VUs over 5 min, hold 5 min, ramp down
 * Finds the breaking point. Watch for 502 (PHP-FPM exhaustion) and 429 (rate limits).
 *
 * k6 run \
 *   -e BASE_URL=https://delni.ly \
 *   -e PROVIDER_SLUG=<slug> \
 *   --out json=results/stress.json \
 *   tests/load/k6-stress.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const BASE_URL      = (__ENV.BASE_URL   || 'http://localhost').replace(/\/$/, '');
const PROVIDER_SLUG = __ENV.PROVIDER_SLUG || 'test-provider';

export const errorRate   = new Rate('errors');
export const homeLatency = new Trend('home_latency_ms');
export const searchLatency = new Trend('search_latency_ms');

export const options = {
  stages: [
    { duration: '1m', target:  10 },
    { duration: '2m', target:  30 },
    { duration: '2m', target:  60 },
    { duration: '5m', target: 100 }, // peak
    { duration: '5m', target: 100 }, // hold
    { duration: '2m', target:   0 }, // ramp down
  ],
  thresholds: {
    http_req_failed:                           ['rate<0.05'],
    errors:                                    ['rate<0.05'],
    'http_req_duration{name:home}':            ['p(95)<2000'],
    'http_req_duration{name:search_keyword}':  ['p(95)<2000'],
    'http_req_duration{name:provider_detail}': ['p(95)<2000'],
    'http_req_duration{name:top_rated}':       ['p(95)<2000'],
  },
};

const HEADERS = { Accept: 'application/json' };

const KEYWORDS = [
  '%D9%83%D9%87%D8%B1%D8%A8%D8%A7%D8%A1',
  '%D8%B3%D8%A8%D8%A7%D9%83',
  '%D8%AA%D9%86%D8%B8%D9%8A%D9%81',
];

export default function () {
  let r;

  r = http.get(`${BASE_URL}/api/v1/home`, { headers: HEADERS, tags: { name: 'home' } });
  homeLatency.add(r.timings.duration);
  check(r, { 'home not 5xx': (res) => res.status < 500 });
  errorRate.add(r.status >= 500);
  sleep(Math.random() * 0.5 + 0.2);

  const kw = KEYWORDS[Math.floor(Math.random() * KEYWORDS.length)];
  r = http.get(
    `${BASE_URL}/api/v1/search?keyword=${kw}&per_page=15&page=${Math.ceil(Math.random() * 3)}`,
    { headers: HEADERS, tags: { name: 'search_keyword' } },
  );
  searchLatency.add(r.timings.duration);
  check(r, { 'search not 5xx': (res) => res.status < 500 });
  errorRate.add(r.status >= 500);
  sleep(Math.random() * 0.5 + 0.2);

  r = http.get(`${BASE_URL}/api/v1/providers/${PROVIDER_SLUG}`, { headers: HEADERS, tags: { name: 'provider_detail' } });
  check(r, { 'provider not 5xx': (res) => res.status < 500 });
  errorRate.add(r.status >= 500);
  sleep(0.2);

  r = http.get(`${BASE_URL}/api/v1/top-rated`, { headers: HEADERS, tags: { name: 'top_rated' } });
  check(r, { 'top-rated not 5xx': (res) => res.status < 500 });
  errorRate.add(r.status >= 500);
  sleep(0.2);
}
