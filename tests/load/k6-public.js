import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Rate } from 'k6/metrics';

export const failedRequests = new Rate('failed_requests');

const BASE_URL = (__ENV.BASE_URL || 'http://localhost').replace(/\/$/, '');
const PROVIDER_SLUG = __ENV.PROVIDER_SLUG || '';

export const options = {
  scenarios: {
    smoke: {
      executor: 'constant-vus',
      vus: 1,
      duration: '1m',
      exec: 'publicMarketplace',
      tags: { scenario_type: 'smoke' },
    },
    light: {
      executor: 'constant-vus',
      vus: 10,
      duration: '2m',
      startTime: '1m',
      exec: 'publicMarketplace',
      tags: { scenario_type: 'light' },
    },
    moderate: {
      executor: 'constant-vus',
      vus: 50,
      duration: '3m',
      startTime: '3m',
      exec: 'publicMarketplace',
      tags: { scenario_type: 'moderate' },
    },
    spike: {
      executor: 'ramping-vus',
      stages: [
        { duration: '20s', target: 10 },
        { duration: '20s', target: 100 },
        { duration: '40s', target: 100 },
        { duration: '20s', target: 0 },
      ],
      startTime: '6m',
      exec: 'publicMarketplace',
      tags: { scenario_type: 'spike' },
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    failed_requests: ['rate<0.01'],
    'http_req_duration{endpoint:home}': ['p(95)<800'],
    'http_req_duration{endpoint:search}': ['p(95)<800'],
    'http_req_duration{endpoint:provider}': ['p(95)<800'],
    'http_req_duration{endpoint:api_search}': ['p(95)<500'],
  },
};

export function publicMarketplace() {
  group('public marketplace', () => {
    request('home', 'GET', '/');
    request('search', 'GET', '/search?per_page=15');
    request('api_search', 'GET', '/api/profiles/search?per_page=15', {
      Accept: 'application/json',
    });

    if (PROVIDER_SLUG !== '') {
      request('provider', 'GET', `/providers/${PROVIDER_SLUG}`);
    }
  });

  sleep(Math.random() * 2 + 1);
}

function request(endpoint, method, path, headers = {}) {
  const response = http.request(method, `${BASE_URL}${path}`, null, {
    headers,
    tags: { endpoint },
  });

  const ok = check(response, {
    [`${endpoint} status is not 5xx`]: (res) => res.status < 500,
    [`${endpoint} status is 2xx/3xx/4xx`]: (res) => res.status >= 200 && res.status < 500,
  });

  failedRequests.add(!ok, { endpoint });

  return response;
}
