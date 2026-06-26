// Realistic public-browse load scenario for the Delni mobile API.
//
// Models how the React Native app actually hits the backend: open home,
// browse categories/top-rated, search, then open a provider detail. Traffic
// is weighted toward the read paths the app calls most.
//
// Uses a ramping-arrival-rate executor so we control REQUESTS PER SECOND
// (not just VU count) — this is what you compare against Laravel's per-minute
// throttle limits and what tells you real backend capacity.
//
//   # local box (raise throttle limits or expect 429s — see README)
//   k6 run -e BASE_URL=http://localhost load-testing/browse.js
//
//   # production, gentle ramp
//   k6 run -e BASE_URL=https://delni.ly load-testing/browse.js
//
//   # crank the target RPS
//   k6 run -e PEAK_RPS=200 load-testing/browse.js
import http from 'k6/http';
import { sleep } from 'k6';
import { API, jsonHeaders, checkRead, pick, SEARCH_TERMS } from './lib/helpers.js';

const PEAK_RPS = parseInt(__ENV.PEAK_RPS || '50', 10);

export const options = {
  scenarios: {
    browse: {
      executor: 'ramping-arrival-rate',
      startRate: 5,
      timeUnit: '1s',
      preAllocatedVUs: 50,
      maxVUs: 500,
      stages: [
        { target: Math.ceil(PEAK_RPS * 0.3), duration: '30s' }, // warm up
        { target: PEAK_RPS, duration: '1m' }, // ramp to peak
        { target: PEAK_RPS, duration: '2m' }, // hold (steady state)
        { target: 0, duration: '30s' }, // ramp down
      ],
    },
  },
  thresholds: {
    // Latency budget measured on successful responses only.
    'http_req_duration{expected_response:true}': ['p(95)<500', 'p(99)<1200'],
    // Real backend failures (5xx) must stay near zero. 429s are tracked
    // separately via the rate_limited metric and are NOT a failure here.
    server_errors_5xx: ['count<1'],
  },
};

// setup() runs once. Discover live slugs/terms from the deployed data so the
// test exercises a SPREAD of records (never hammer one cached id — that
// produces fake numbers, per k6 best practice).
export function setup() {
  const opts = jsonHeaders();

  const categories = (http.get(`${API}/categories`, opts).json('data') || [])
    .map((c) => c.slug)
    .filter(Boolean);

  const providers = (http.get(`${API}/search?per_page=30`, opts).json('data') || [])
    .map((p) => p.slug)
    .filter(Boolean);

  console.log(`Discovered ${categories.length} categories, ${providers.length} providers.`);
  if (providers.length === 0) {
    console.warn('No discoverable providers — provider-detail path will be skipped. Seed data first.');
  }

  return { categories, providers };
}

export default function (data) {
  const opts = jsonHeaders();

  // 1. Home feed — every session starts here.
  checkRead(http.get(`${API}/home`, opts), 'home');
  sleep(0.5);

  // 2. Branch into a weighted browse action.
  const roll = Math.random();

  if (roll < 0.4) {
    // 40% — keyword search (heaviest query path: FULLTEXT + joins + ranking)
    const term = encodeURIComponent(pick(SEARCH_TERMS));
    checkRead(http.get(`${API}/search?q=${term}&per_page=15`, opts), 'search');
    checkRead(http.get(`${API}/search/suggestions?q=${term}`, opts), 'suggestions');
  } else if (roll < 0.7) {
    // 30% — top-rated listing
    checkRead(http.get(`${API}/top-rated?per_page=15`, opts), 'top-rated');
  } else {
    // 30% — category landing
    checkRead(http.get(`${API}/categories`, opts), 'categories');
    if (data.categories.length) {
      checkRead(http.get(`${API}/categories/${pick(data.categories)}`, opts), 'category-detail');
    }
  }
  sleep(0.5);

  // 3. ~60% of sessions open a provider detail (the relation-heavy endpoint).
  if (data.providers.length && Math.random() < 0.6) {
    const slug = pick(data.providers);
    checkRead(http.get(`${API}/providers/${slug}`, opts), 'provider-detail');
    checkRead(http.get(`${API}/providers/${slug}/reviews?per_page=10`, opts), 'provider-reviews');
  }

  sleep(1);
}
