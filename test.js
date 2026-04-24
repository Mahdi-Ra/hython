import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend } from 'k6/metrics';

// متریک جدا برای هر صفحه
let homeTrend = new Trend('home_page');
let shopTrend = new Trend('shop_page');
let cartTrend = new Trend('cart_page');
let productTrends = {}; // دیکشنری برای محصولات

// لیست URL محصولات پر فروش (می‌تونی خودت اضافه یا کم کنی)
const products = [
    'https://fidarsystem.com/shop/acrylic-cast-clear/?attribute_pa_thickness-inches=3-16-approximately-equal-to-4-3mm&attribute_pa_dimension-inch=48-96-inch',
    'https://fidarsystem.com/shop/acrylic-cast-clear/?attribute_pa_thickness-inches=1-4&attribute_pa_dimension-inch=24-48-inch',
    // محصول سوم و چهارم...
];

// ایجاد trend برای هر محصول
products.forEach((url, index) => {
    productTrends[url] = new Trend(`product_${index+1}`);
});

export let options = {
    stages: [
        { duration: '30s', target: 1 },   // warm-up
        { duration: '1m', target: 5 },    // فشار متوسط
        { duration: '1m', target: 10 },   // فشار بالا
        { duration: '30s', target: 0 },   // cool-down
    ],
    thresholds: {
        'http_req_failed': ['rate<0.1'], // هشدار اگر بیش از 10% fail شد
    }
};

export default function () {
    // -------------------------
    // 🏠 Homepage
    let home = http.get('https://fidarsystem.com/');
    homeTrend.add(home.timings.duration);
    check(home, { 'home status 200': (r) => r.status === 200 });
    sleep(1);

    // -------------------------
    // 🛍️ Shop page
    let shop = http.get('https://fidarsystem.com/shop/');
    shopTrend.add(shop.timings.duration);
    check(shop, { 'shop status 200': (r) => r.status === 200 });
    sleep(1);

    // -------------------------
    // 📦 Product pages
    products.forEach((url) => {
        let res = http.get(url);
        productTrends[url].add(res.timings.duration);
        check(res, { [`product status 200: ${url}`]: (r) => r.status === 200 });
        sleep(1);
    });

    // -------------------------
    // 🛒 Cart page
    let cart = http.get('https://fidarsystem.com/cart/');
    cartTrend.add(cart.timings.duration);
    check(cart, { 'cart status 200': (r) => r.status === 200 });
    sleep(1);
}