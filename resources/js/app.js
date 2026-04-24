import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const jalaliBreaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];

function div(a, b) {
    return ~~(a / b);
}

function jalCal(jy) {
    let bl = jalaliBreaks.length;
    let gy = jy + 621;
    let leapJ = -14;
    let jp = jalaliBreaks[0];
    let jm;
    let jump;
    let leap;
    let leapG;
    let march;
    let n;
    let i;

    if (jy < jp || jy >= jalaliBreaks[bl - 1]) {
        throw new Error('Invalid Jalali year');
    }

    for (i = 1; i < bl; i += 1) {
        jm = jalaliBreaks[i];
        jump = jm - jp;
        if (jy < jm) {
            break;
        }
        leapJ += div(jump, 33) * 8 + div((jump % 33), 4);
        jp = jm;
    }
    n = jy - jp;
    leapJ += div(n, 33) * 8 + div(((n % 33) + 3), 4);
    if ((jump % 33) === 4 && (jump - n) === 4) {
        leapJ += 1;
    }
    leapG = div(gy, 4) - div(((div(gy, 100) + 1) * 3), 4) - 150;
    march = 20 + leapJ - leapG;
    if ((jump - n) < 6) {
        n = n - jump + div((jump + 4), 33) * 33;
    }
    leap = (((n + 1) % 33) - 1) % 4;
    if (leap === -1) {
        leap = 4;
    }

    return { leap, gy, march };
}

function g2d(gy, gm, gd) {
    let d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
        + div(153 * ((gm + 9) % 12) + 2, 5)
        + gd - 34840408;
    d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
    return d;
}

function d2g(jdn) {
    let j = 4 * jdn + 139361631;
    j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
    let i = div((j % 1461), 4) * 5 + 308;
    let gd = div(i % 153, 5) + 1;
    let gm = (div(i, 153) % 12) + 1;
    let gy = div(j, 1461) - 100100 + div(8 - gm, 6);

    return { gy, gm, gd };
}

function j2d(jy, jm, jd) {
    const r = jalCal(jy);
    return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
}

function d2j(jdn) {
    const gDate = d2g(jdn);
    let jy = gDate.gy - 621;
    const r = jalCal(jy);
    const jdn1f = g2d(gDate.gy, 3, r.march);
    let k = jdn - jdn1f;
    let jd;
    let jm;

    if (k >= 0) {
        if (k <= 185) {
            jm = 1 + div(k, 31);
            jd = (k % 31) + 1;
            return { jy, jm, jd };
        }
        k -= 186;
    } else {
        jy -= 1;
        k += 179;
        if (r.leap === 1) {
            k += 1;
        }
    }

    jm = 7 + div(k, 30);
    jd = (k % 30) + 1;
    return { jy, jm, jd };
}

function gregorianToJalali(gy, gm, gd) {
    return d2j(g2d(gy, gm, gd));
}

function jalaliToGregorian(jy, jm, jd) {
    return d2g(j2d(jy, jm, jd));
}

function pad(value) {
    return String(value).padStart(2, '0');
}

function formatJalaliFromGregorian(value) {
    if (!value) {
        return '';
    }

    const [gy, gm, gd] = value.split('-').map(Number);
    if (!gy || !gm || !gd) {
        return '';
    }

    const jalali = gregorianToJalali(gy, gm, gd);
    return `${jalali.jy}/${pad(jalali.jm)}/${pad(jalali.jd)}`;
}

function formatGregorianFromJalali(value) {
    if (!value) {
        return '';
    }

    const normalized = value.replaceAll('-', '/');
    const [jy, jm, jd] = normalized.split('/').map(Number);
    if (!jy || !jm || !jd) {
        return '';
    }

    const gregorian = jalaliToGregorian(jy, jm, jd);
    return `${gregorian.gy}-${pad(gregorian.gm)}-${pad(gregorian.gd)}`;
}

function initJalaliInputs() {
    document.querySelectorAll('[data-jalali-filter-form], [data-jalali-form]').forEach((form) => {
        const visibleInputs = form.querySelectorAll('[data-jalali-visible]');

        visibleInputs.forEach((input) => {
            const hiddenName = input.dataset.jalaliVisible;
            const hiddenInput = form.querySelector(`input[name="${hiddenName}"]`);
            if (!hiddenInput) {
                return;
            }

            input.value = formatJalaliFromGregorian(hiddenInput.value);

            input.addEventListener('blur', () => {
                hiddenInput.value = formatGregorianFromJalali(input.value);
                if (hiddenInput.value) {
                    input.value = formatJalaliFromGregorian(hiddenInput.value);
                }
            });
        });

        form.addEventListener('submit', () => {
            visibleInputs.forEach((input) => {
                const hiddenName = input.dataset.jalaliVisible;
                const hiddenInput = form.querySelector(`input[name="${hiddenName}"]`);
                if (!hiddenInput) {
                    return;
                }

                hiddenInput.value = formatGregorianFromJalali(input.value);
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', initJalaliInputs);
