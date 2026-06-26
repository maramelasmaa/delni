// Smoke test — run this FIRST, before any load scenario.
//
// 1 virtual user hits every public read endpoint once and asserts it responds
// 2xx. Its job is to prove the deployment is healthy and the test rig can
// reach it — not to generate load. If smoke fails, do not bother load testing.
//
//   k6 run load-testing/smoke.js
//   k6 run -e BASE_URL=https://staging.delni.ly load-testing/smoke.js
import http from 'k6/http';
import { check } from 'k6';
import { API, jsonHeaders } from './lib/helpers.js';

export const options = {
  vus: 1,
  iterations: 1,
  thresholds: {
    http_req_failed: ['rate==0'], // every smoke request must succeed
  },
};

export default function () {
  const opts = jsonHeaders();

  // Health
  check(http.get(`${API}/health`, opts), { 'health 200': (r) => r.status === 200 });

  // Reference data
  check(http.get(`${API}/home`, opts), { 'home 200': (r) => r.status === 200 });
  check(http.get(`${API}/categories`, opts), { 'categories 200': (r) => r.status === 200 });
  check(http.get(`${API}/cities`, opts), { 'cities 200': (r) => r.status === 200 });
  check(http.get(`${API}/provider-types`, opts), { 'provider-types 200': (r) => r.status === 200 });
  check(http.get(`${API}/contact`, opts), { 'contact 200': (r) => r.status === 200 });
  check(http.get(`${API}/top-rated`, opts), { 'top-rated 200': (r) => r.status === 200 });

  // Search (validate the response shape we depend on in browse.js setup)
  const search = http.get(`${API}/search?per_page=5`, opts);
  check(search, {
    'search 200': (r) => r.status === 200,
    'search returns data[]': (r) => Array.isArray(r.json('data')),
  });

  // Drill into the first provider slug if any seeded data exists
  const first = search.json('data.0.slug');
  if (first) {
    check(http.get(`${API}/providers/${first}`, opts), { 'provider detail 200': (r) => r.status === 200 });
    check(http.get(`${API}/providers/${first}/reviews`, opts), { 'provider reviews 200': (r) => r.status === 200 });
  } else {
    console.warn('No providers returned by /search — seed discoverable providers before load testing.');
  }
}
