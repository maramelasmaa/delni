import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const errorRate = new Rate('errors');
const homeTrend = new Trend('home_duration', true);
const searchTrend = new Trend('search_duration', true);
const providerTrend = new Trend('provider_duration', true);

const BASE = __ENV.BASE || 'http://localhost:8080';

export const options = {
    stages: [
        { duration: '30s', target: 10 },   // ramp up to 10 VUs
        { duration: '1m',  target: 10 },   // hold 10 VUs for 1 min
        { duration: '30s', target: 30 },   // ramp up to 30 VUs
        { duration: '1m',  target: 30 },   // hold 30 VUs
        { duration: '30s', target: 0 },    // ramp down
    ],
    thresholds: {
        http_req_failed:   ['rate<0.05'],         // <5% errors
        http_req_duration: ['p(95)<3000'],        // 95% of requests under 3s
        home_duration:     ['p(95)<2000'],        // homepage p95 under 2s
        search_duration:   ['p(95)<3000'],        // search API p95 under 3s
        provider_duration: ['p(95)<2500'],        // provider page p95 under 2.5s
    },
};

// Seed a provider slug from the API on startup
let cachedSlug = null;

export function setup() {
    const res = http.get(`${BASE}/api/profiles/search?q=`, { timeout: '10s' });
    if (res.status === 200) {
        const body = JSON.parse(res.body);
        if (body.data && body.data.length > 0) {
            return { slug: body.data[0].slug };
        }
    }
    return { slug: null };
}

export default function (data) {
    const slug = data.slug;

    // 1. Homepage
    const home = http.get(`${BASE}/`, { timeout: '10s' });
    homeTrend.add(home.timings.duration);
    errorRate.add(home.status !== 200);
    check(home, { 'home 200': (r) => r.status === 200 });

    sleep(0.5);

    // 2. Search API
    const q = ['web', 'design', 'photo', 'video', ''][Math.floor(Math.random() * 5)];
    const search = http.get(`${BASE}/api/profiles/search?q=${q}`, {
        headers: { Accept: 'application/json' },
        timeout: '10s',
    });
    searchTrend.add(search.timings.duration);
    errorRate.add(search.status !== 200);
    check(search, {
        'search 200': (r) => r.status === 200,
        'search returns data': (r) => {
            try { return JSON.parse(r.body).data !== undefined; } catch { return false; }
        },
    });

    sleep(0.3);

    // 3. Provider page (if we have a slug)
    if (slug) {
        const provider = http.get(`${BASE}/providers/${slug}`, { timeout: '10s' });
        providerTrend.add(provider.timings.duration);
        errorRate.add(provider.status !== 200 && provider.status !== 302);
        check(provider, { 'provider page 2xx': (r) => r.status < 400 });
    }

    sleep(Math.random() * 0.5);
}
