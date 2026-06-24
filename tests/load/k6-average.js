/**
 * Average Load Test — 20 VUs, ramp 2m / steady 5m / ramp-down 1m
 * Simulates a normal day's traffic: guest browsing + authenticated favorites check.
 * Thresholds: http_req_failed < 1%, p(95) < 800ms, p(99) < 1500ms
 *
 * k6 run \
 *   -e BASE_URL=https://delni.ly \
 *   -e PROVIDER_SLUG=<slug> \
 *   -e CATEGORY_SLUG=<slug> \
 *   -e CITY_SLUG=tripoli \
 *   -e AUTH_TOKEN=<sanctum-token> \
 *   tests/load/k6-average.js
 */
import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const BASE_URL      = (__ENV.BASE_URL   || 'http://localhost').replace(/\/$/, '');
const PROVIDER_SLUG = __ENV.PROVIDER_SLUG || 'test-provider';
const CATEGORY_SLUG = __ENV.CATEGORY_SLUG || 'test-category';
const CITY_SLUG     = __ENV.CITY_SLUG    || 'tripoli';
const AUTH_TOKEN    = __ENV.AUTH_TOKEN   || '';

export const errorRate = new Rate('errors');

export const options = {
  stages: [
    { duration: '2m', target: 20 },
    { duration: '5m', target: 20 },
    { duration: '1m', target:  0 },
  ],
  thresholds: {
    http_req_failed:                              ['rate<0.01'],
    errors:                                       ['rate<0.01'],
    'http_req_duration{name:home}':               ['p(95)<800',  'p(99)<1500'],
    'http_req_duration{name:categories}':         ['p(95)<500',  'p(99)<800'],
    'http_req_duration{name:search_keyword}':     ['p(95)<800',  'p(99)<1500'],
    'http_req_duration{name:search_city_filter}': ['p(95)<800',  'p(99)<1500'],
    'http_req_duration{name:provider_detail}':    ['p(95)<800',  'p(99)<1500'],
    'http_req_duration{name:top_rated}':          ['p(95)<800',  'p(99)<1500'],
    'http_req_duration{name:category_browse}':    ['p(95)<1000', 'p(99)<1500'],
    'http_req_duration{name:favorites}':          ['p(95)<600',  'p(99)<1000'],
  },
};

const HEADERS = { Accept: 'application/json' };
const AUTH_HEADERS = AUTH_TOKEN
  ? { ...HEADERS, Authorization: `Bearer ${AUTH_TOKEN}` }
  : HEADERS;

// Arabic search keywords (URL-encoded)
const KEYWORDS = [
  '%D9%83%D9%87%D8%B1%D8%A8%D8%A7%D8%A1',  // كهرباء
  '%D8%B3%D8%A8%D8%A7%D9%83',              // سباك
  '%D8%AA%D9%86%D8%B8%D9%8A%D9%81',       // تنظيف
  '%D8%B7%D8%A8%D8%A7%D8%AE',             // طباخ
  '%D9%86%D8%AC%D8%A7%D8%B1',             // نجار
];

export default function () {
  group('guest: homepage flow', () => {
    const r = http.get(`${BASE_URL}/api/home`, { headers: HEADERS, tags: { name: 'home' } });
    check(r, { 'home 200': (res) => res.status === 200 });
    errorRate.add(r.status !== 200);
    sleep(Math.random() * 2 + 1);
  });

  group('guest: browse categories', () => {
    const r = http.get(`${BASE_URL}/api/categories`, { headers: HEADERS, tags: { name: 'categories' } });
    check(r, { 'categories 200': (res) => res.status === 200 });
    errorRate.add(r.status !== 200);
    sleep(1);
  });

  group('guest: keyword search', () => {
    const kw = KEYWORDS[Math.floor(Math.random() * KEYWORDS.length)];
    const r  = http.get(
      `${BASE_URL}/api/search?keyword=${kw}&per_page=15`,
      { headers: HEADERS, tags: { name: 'search_keyword' } },
    );
    check(r, { 'search 200': (res) => res.status === 200 });
    errorRate.add(r.status !== 200);
    sleep(Math.random() * 2 + 1);
  });

  group('guest: search with city filter', () => {
    const r = http.get(
      `${BASE_URL}/api/search?city=${CITY_SLUG}&sort=rating&per_page=15`,
      { headers: HEADERS, tags: { name: 'search_city_filter' } },
    );
    check(r, { 'search+city 200': (res) => res.status === 200 });
    errorRate.add(r.status !== 200);
    sleep(Math.random() + 1);
  });

  group('guest: browse category', () => {
    const r = http.get(
      `${BASE_URL}/api/categories/${CATEGORY_SLUG}`,
      { headers: HEADERS, tags: { name: 'category_browse' } },
    );
    check(r, { 'category 2xx': (res) => res.status < 300 });
    sleep(Math.random() + 1);
  });

  group('guest: view provider', () => {
    const r = http.get(
      `${BASE_URL}/api/providers/${PROVIDER_SLUG}`,
      { headers: HEADERS, tags: { name: 'provider_detail' } },
    );
    check(r, { 'provider not 5xx': (res) => res.status < 500 });
    errorRate.add(r.status >= 500);
    sleep(Math.random() * 2 + 1);
  });

  group('guest: top rated', () => {
    const r = http.get(`${BASE_URL}/api/top-rated`, { headers: HEADERS, tags: { name: 'top_rated' } });
    check(r, { 'top-rated 200': (res) => res.status === 200 });
    errorRate.add(r.status !== 200);
    sleep(1);
  });

  if (AUTH_TOKEN) {
    group('auth: check favorites', () => {
      const r = http.get(`${BASE_URL}/api/favorites`, { headers: AUTH_HEADERS, tags: { name: 'favorites' } });
      check(r, { 'favorites 200': (res) => res.status === 200 });
      errorRate.add(r.status >= 500);
      sleep(1);
    });
  }
}
