import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend } from 'k6/metrics';

let productTrend = new Trend('product_page');

export let options = {
  vus: 50,          // 👈 تعداد یوزر همزمان
  duration: '1m',   // 👈 مدت تست (بهتر از 30s)
};

export default function () {
  let res = http.get(
    'https://fidarsystem.com/shop/acrylic-cast-clear/',
    {
      headers: {
        'User-Agent': 'Mozilla/5.0 (k6 load test)', // 👈 واقعی‌تر
      },
    }
  );

  productTrend.add(res.timings.duration);

  check(res, {
    'status is 200': (r) => r.status === 200,
  });

  sleep(1); // فاصله بین درخواست‌ها
}