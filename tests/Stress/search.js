// Satu sesi login dibuat di setup(), lalu cookie dibagikan ke setiap VU.
// Ini menghindari rate limit login karena semua VU berasal dari IP yang sama.
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend } from 'k6/metrics';

const searchDuration = new Trend('search_duration_ms');

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';
const EMAIL = __ENV.PMS_EMAIL || 'admin@example.com';
const PASSWORD = __ENV.PMS_PASSWORD || 'password';
const SEARCH_VUS = Number(__ENV.SEARCH_VUS || 10);
const CATALOG_VUS = Number(__ENV.CATALOG_VUS || 5);
const DURATION = __ENV.DURATION || '30s';

const SEARCH_TERMS = [
    'Stress 0001',
    'Stress 0005',
    'STR-000123',
    'STR-049999',
    'laptop',
    '%',
];

export const options = {
    scenarios: {
        search: {
            executor: 'constant-vus',
            vus: SEARCH_VUS,
            duration: DURATION,
            exec: 'searchScenario',
        },
        catalog: {
            executor: 'constant-vus',
            vus: CATALOG_VUS,
            duration: DURATION,
            exec: 'catalogScenario',
            startTime: '2s',
        },
    },
    thresholds: {
        'http_req_failed': ['rate<0.01'],
        'search_duration_ms': ['p(95)<300'],
        'http_req_duration{scenario:catalog}': ['p(95)<2000'],
    },
};

export function setup() {
    const loginPage = http.get(`${BASE_URL}/login`);
    const token = loginPage.html('input[name="_token"]').first().attr('value');

    check(loginPage, {
        'halaman login tampil': (response) => response.status === 200 && token !== undefined,
    });

    const response = http.post(
        `${BASE_URL}/login`,
        { _token: token, email: EMAIL, password: PASSWORD },
        {
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            redirects: 0,
        },
    );

    check(response, {
        'login berhasil': (result) => result.status === 302,
    });

    const cookies = {};

    Object.entries(response.cookies).forEach(([name, values]) => {
        if (values.length > 0) {
            cookies[name] = values[0].value;
        }
    });

    return { cookies };
}

function applyCookies(data) {
    const jar = http.cookieJar();

    Object.entries(data.cookies).forEach(([name, value]) => {
        jar.set(BASE_URL, name, value);
    });
}

export function searchScenario(data) {
    applyCookies(data);

    const term = SEARCH_TERMS[Math.floor(Math.random() * SEARCH_TERMS.length)];
    const response = http.get(`${BASE_URL}/products?q=${encodeURIComponent(term)}&sort=name_asc`);
    const durationHeader = response.headers['X-Search-Duration-Ms'] || response.headers['X-Search-Duration-ms'];

    if (durationHeader !== undefined) {
        searchDuration.add(Number(durationHeader));
    }

    check(response, {
        'hasil pencarian tampil': (result) => result.status === 200 && result.body.includes('Daftar Produk'),
    });

    sleep(0.2);
}

export function catalogScenario(data) {
    applyCookies(data);

    const page = 1 + Math.floor(Math.random() * 20);
    const response = http.get(`${BASE_URL}/products?page=${page}`);

    check(response, {
        'katalog tampil': (result) => result.status === 200 && result.body.includes('Daftar Produk'),
    });

    sleep(0.5);
}
