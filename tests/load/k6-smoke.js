/**
 * Smoke Test — 1 VU, 1 minute
 * Confirms basic availability of the 5 most-trafficked endpoints.
 * Run before every other k6 test. If this fails, stop and fix first.
 *
 * k6 run -e BASE_URL=https://delni.ly -e PROVIDER_SLUG=<slug> tests/load/k6-smoke.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL   = (__ENV.BASE_URL   || 'http://localhost').replace(/\/$/, '');
const PROVIDER_SLUG = __ENV.PROVIDER_SLUG || 'test-provider';
const CATEGORY_SLUG = __ENV.CATEGORY_SLUG || 'test-category';

export const options = {
  vus: 1,
  duration: '1m',
  thresholds: {
    http_req_failed:                          ['rate<0.01'],
    'http_req_duration{name:home}':           ['p(95)<2000'],
    'http_req_duration{name:categories}':     ['p(95)<1000'],
    'http_req_duration{name:search_blank}':   ['p(95)<2000'],
    'http_req_duration{name:provider_detail}':['p(95)<2000'],
    'http_req_duration{name:top_rated}':      ['p(95)<2000'],
  },
};

const JSON_HEADERS = { Accept: 'application/json' };

export default function () {
  let r;

  // 1. Home — most frequented endpoint
  r = http.get(`${BASE_URL}/api/home`, { headers: JSON_HEADERS, tags: { name: 'home' } });
  check(r, { 'home: status 200': (res) => res.status === 200 });
  sleep(1);

  // 2. Categories — lightweight, cached
  r = http.get(`${BASE_URL}/api/categories`, { headers: JSON_HEADERS, tags: { name: 'categories' } });
  check(r, { 'categories: status 200': (res) => res.status === 200 });
  sleep(1);

  // 3. Search — blank query (worst-case no FULLTEXT optimisation)
  r = http.get(`${BASE_URL}/api/search?per_page=15`, { headers: JSON_HEADERS, tags: { name: 'search_blank' } });
  check(r, { 'search_blank: status 200': (res) => res.status === 200 });
  sleep(1);

  // 4. Provider detail — heaviest single-page query
  r = http.get(`${BASE_URL}/api/providers/${PROVIDER_SLUG}`, { headers: JSON_HEADERS, tags: { name: 'provider_detail' } });
  check(r, { 'provider_detail: not 5xx': (res) => res.status < 500 });
  sleep(1);

  // 5. Top-rated — live ranking, no cache
  r = http.get(`${BASE_URL}/api/top-rated`, { headers: JSON_HEADERS, tags: { name: 'top_rated' } });
  check(r, { 'top_rated: status 200': (res) => res.status === 200 });
  sleep(1);
}
