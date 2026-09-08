const puppeteer = require('puppeteer-core');

const BASE = 'http://127.0.0.1:8000';
const OUT = 'C:/Users/ASUS/KosManager/tmp-qa';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const VIEWPORTS = [
  { width: 360, height: 780, tag: '360' },
  { width: 390, height: 844, tag: '390' },
  { width: 430, height: 932, tag: '430' },
  { width: 768, height: 1024, tag: '768' },
  { width: 1280, height: 900, tag: '1280' },
];

(async () => {
  const fs = require('fs');
  const path = require('path');
  fs.mkdirSync(OUT, { recursive: true });

  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    defaultViewport: null,
    args: ['--no-sandbox', '--disable-gpu', '--force-device-scale-factor=1'],
  });

  const report = [];
  const results = [];
  const page = await browser.newPage();

  const shot = async (name, opts = {}) => {
    await page.screenshot({ path: path.join(OUT, name), fullPage: opts.fullPage !== false });
    report.push(name);
  };

  const errors = [];
  page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));
  page.on('console', (m) => {
    if (m.type() === 'error') {
      const t = m.text();
      if (!/favicon|net::ERR_/.test(t)) errors.push('CONSOLE: ' + t);
    }
  });

  const goto = async (url) => {
    for (let i = 0; i < 3; i++) {
      try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await new Promise((r) => setTimeout(r, 800));
        return;
      } catch (e) {
        if (i === 2) throw e;
        await new Promise((r) => setTimeout(r, 500));
      }
    }
  };

  const setViewport = async (vp) => {
    await page.setViewport({ width: vp.width, height: vp.height });
    await new Promise((r) => setTimeout(r, 150));
  };

  // ---- Login as tenant ----
  await goto(BASE + '/login');
  const emailSel = await page.$('input[name="email"]') || await page.$('#email');
  await emailSel.type('tenant@example.com');
  await page.$eval('input[name="password"]', (el) => el && el.focus());
  await page.type('input[name="password"], #password', 'password');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }),
    page.click('button[type="submit"]'),
  ]).catch((e) => { errors.push('loginnav: ' + e.message); });
  await new Promise((r) => setTimeout(r, 600));
  report.push('URL-AFTER-LOGIN: ' + page.url());

  // ============ 1. TENANT DASHBOARD (banner + card ads) ============
  for (const vp of VIEWPORTS) {
    await setViewport(vp);
    await goto(BASE + '/dashboard');

    const hasBanner = !!(await page.$('section[aria-label="Promo & Partner Pilihan"] .group'));
    const hasCarousel = await page.evaluate(() => {
      const el = document.querySelector('section[aria-label="Promo & Partner Pilihan"] [x-data]');
      return !!(el && (el.getAttribute('x-data') || '').includes('total:'));
    });
    const adCards = await page.$$eval('section[aria-label="Penawaran partner untuk penghuni kos"] a', (els) => els.length);

    await page.evaluate(() => {
      const s = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
      if (s) s.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 250));
    await shot('dashboard_banner_' + vp.tag + '.png');

    if (await page.$('section[aria-label="Penawaran partner untuk penghuni kos"]')) {
      await page.evaluate(() => {
        const s = document.querySelector('section[aria-label="Penawaran partner untuk penghuni kos"]');
        if (s) s.scrollIntoView({ block: 'start' });
      });
      await new Promise((r) => setTimeout(r, 250));
      await shot('dashboard_cards_' + vp.tag + '.png');
    }

    results.push(`DASHBOARD ${vp.tag}: banner=${hasBanner} carousel=${hasCarousel} adCards=${adCards}`);
  }

  // ============ 2. MARKETPLACE (compact) ============
  for (const vp of VIEWPORTS) {
    await setViewport(vp);
    await goto(BASE + '/tenant/kos');

    const ads = await page.$$eval('section[aria-label="Partner untuk penghuni kos"] a', (els) =>
      els.map((e) => e.innerText.trim().replace(/\s+/g, ' ')));

    await page.evaluate(() => {
      const s = document.querySelector('section[aria-label="Partner untuk penghuni kos"]');
      if (s) s.scrollIntoView({ block: 'start' });
    });
    await new Promise((r) => setTimeout(r, 250));
    await shot('marketplace_ads_' + vp.tag + '.png');

    results.push(`MARKETPLACE ${vp.tag}: ads=${ads.length} first="${(ads[0] || '').slice(0, 110)}"`);
  }

  // ============ 3. KOS DETAIL (compact) ============
  for (const vp of VIEWPORTS) {
    await setViewport(vp);
    await goto(BASE + '/tenant/kos/1');

    const adAnchors = await page.$$eval('a[href*="ad/click"], a[aria-label*="Selengkapnya"]', (els) => els.length);

    await page.evaluate(() => {
      const el = document.querySelector('a[href*="ad/click"]') || document.querySelector('a[aria-label*="Selengkapnya"]');
      if (el) el.scrollIntoView({ block: 'center' });
    });
    await new Promise((r) => setTimeout(r, 250));
    await shot('kos_detail_ads_' + vp.tag + '.png');

    results.push(`KOS-DETAIL ${vp.tag}: adAnchors=${adAnchors}`);
  }

  // ============ 4. Desktop full pages ============
  await setViewport(VIEWPORTS[4]);
  await goto(BASE + '/dashboard');
  await shot('desktop_dashboard_full_1280.png');
  await goto(BASE + '/tenant/kos');
  await shot('desktop_marketplace_full_1280.png');
  await goto(BASE + '/tenant/kos/1');
  await shot('desktop_kosdetail_full_1280.png');

  // ============ 5. Carousel interaction (mobile) ============
  await setViewport(VIEWPORTS[1]);
  await goto(BASE + '/dashboard');
  const before = await page.evaluate(() => {
    const dots = document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button');
    const xd = document.querySelector('section[aria-label="Promo & Partner Pilihan"] [x-data]');
    return {
      dotCount: dots.length,
      arrow: !!document.querySelector('section[aria-label="Promo & Partner Pilihan"] button[aria-label="Slide berikutnya"]'),
      xdata: xd ? (xd.getAttribute('x-data') || '').slice(0, 50) : null,
      alpineDefined: typeof window.Alpine !== 'undefined',
    };
  });

  let clicked = false;
  const activeDot = await page.evaluate(() => {
    const dots = Array.from(document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button'));
    if (dots.length < 2) return { active: -2, transform: 'nil', clicked: false };
    dots[1].click(); // non-active dot (index 0 is active initially)
    return new Promise((resolve) => setTimeout(() => {
      const cur = Array.from(document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button'));
      const active = cur.findIndex((d) => d.getAttribute('aria-current') === 'true');
      const rail = document.querySelector('section[aria-label="Promo & Partner Pilihan"] div.flex.transition-transform');
      resolve({ active, transform: rail ? rail.style.transform : 'nil', clicked: true });
    }, 800));
  });
  clicked = activeDot.clicked === true;

  results.push(`CAROUSEL 390: dots=${before.dotCount} arrow=${before.arrow} clicked=${clicked} activeDotAfter=${activeDot.active} transform=${activeDot.transform} alpine=${before.alpineDefined} xdata="${before.xdata || 'N/A'}"`);
  await page.screenshot({ path: path.join(OUT, 'dashboard_after_click_390.png'), fullPage: false });

  // ============ 6. Bottom nav overlap check ============
  await goto(BASE + '/dashboard');
  const bottom = await page.evaluate(() => {
    const nav = document.querySelector('[data-tenant-bottom-nav]') || document.querySelector('nav.fixed, nav.sticky');
    let out = nav ? nav.className : 'no nav';
    const promo = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    return { navClass: out.slice(0, 80), promoInView: !!promo };
  });
  results.push(`BOTTOMNAV: ${bottom.navClass} promoSection=${bottom.promoInView}`);

  results.push('ERRORS: ' + (errors.length ? errors.join(' | ') : 'none'));
  console.log(JSON.stringify({ report, results }, null, 2));
  await browser.close();
})().catch((e) => { console.error('QA_FAILED', e); process.exit(1); });