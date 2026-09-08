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
  const page = await browser.newPage();
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
        await new Promise((r) => setTimeout(r, 700));
        return;
      } catch (e) {
        if (i === 2) throw e;
        await new Promise((r) => setTimeout(r, 500));
      }
    }
  };
  const setViewport = async (vp) => {
    await page.setViewport({ width: vp.width, height: vp.height });
    await new Promise((r) => setTimeout(r, 120));
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
  ]).catch((e) => errors.push('loginnav: ' + e.message));
  await new Promise((r) => setTimeout(r, 600));

  const results = [];
  const report = [];
  const shot = async (name) => {
    await page.screenshot({ path: path.join(OUT, name), fullPage: false });
    report.push(name);
  };

  // ---- Detail pages: destination per kos ----
  const expected = {
    1: '-7.7956000,110.3695000',        // coordinates
    2: '-7.7821000,110.3675000',        // coordinates (different)
    3: 'Jl.+Gatot+Subroto+No.+50',      // address fallback
    4: null,                            // no location
  };

  for (const vp of VIEWPORTS) {
    await setViewport(vp);
    for (const id of [1, 2, 3, 4]) {
      await goto(BASE + '/tenant/kos/' + id);
      const info = await page.evaluate((exp) => {
        const wrap = document.querySelector('section[aria-label="Lokasi Kos"], div');
        const link = Array.from(document.querySelectorAll('a[href*="maps/dir"]')).find((a) =>
          a.innerHTML.includes('navigation') || a.textContent.trim().toLowerCase().includes('arah')
        );
        const href = link ? link.getAttribute('href') : null;
        const label = link ? (link.getAttribute('aria-label') || link.textContent.trim().replace(/\s+/g, ' ')) : null;
        const target = link ? link.getAttribute('target') : null;
        const rel = link ? link.getAttribute('rel') : null;
        return {
          href,
          label,
          target,
          rel,
          overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
          hasMapIframe: !!document.querySelector('iframe[src*="maps"]'),
          hasEmptyState: document.body.textContent.includes('Lokasi peta belum tersedia'),
        };
      }, expected[id]);

      const dest = info.href ? decodeURIComponent(info.href.split('destination=')[1] || '') : null;
      const passDest = expected[id] === null ? dest === null : dest === decodeURIComponent(expected[id]);
      results.push(`DETAIL id=${id} [${vp.tag}] dest=${dest ? ('"' + dest + '"') : 'NULL'} expected=${expected[id] ? '"' + decodeURIComponent(expected[id]) + '"' : 'NULL'} ${passDest ? 'OK' : 'MISMATCH'} aria="${info.label}" target=${info.target} rel=${info.rel} overflow=${info.overflow} iframe=${info.hasMapIframe} empty=${info.hasEmptyState}`);
    }
  }

  // ---- Marketplace + dashboard cards ----
  for (const vp of VIEWPORTS) {
    await setViewport(vp);
    await goto(BASE + '/tenant/kos');
    const cards = await page.evaluate(() => {
      const out = [];
      document.querySelectorAll('a[href*="maps/dir"]').forEach((a) => {
        out.push({ href: a.getAttribute('href'), text: a.textContent.trim().replace(/\s+/g, ' ') });
      });
      return {
        count: out.length,
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
        links: out.slice(0, 5),
      };
    });
    results.push(`MARKETPLACE [${vp.tag}] arahLinks=${cards.count} overflow=${cards.overflow} ${cards.links.map(l => '"' + l.text + '"→' + (l.href.split('destination=')[1] || '')).join(' | ')}`);
    await page.evaluate(() => {
      const el = document.querySelector('a[href*="maps/dir"]');
      if (el) el.scrollIntoView({ block: 'center' });
    });
    await new Promise((r) => setTimeout(r, 250));
    await shot('gmaps_marketplace_' + vp.tag + '.png');

    await goto(BASE + '/dashboard');
    const dash = await page.evaluate(() => {
      document.querySelectorAll('a[href*="maps/dir"]').forEach((a) => {});
      return {
        count: document.querySelectorAll('a[href*="maps/dir"]').length,
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
      };
    });
    results.push(`DASHBOARD [${vp.tag}] arahLinks=${dash.count} overflow=${dash.overflow}`);
  }

  // ---- Booking create (address CTA) ----
  await setViewport(VIEWPORTS[1]);
  await goto(BASE + '/tenant/booking/create?kos_id=1');
  const booking = await page.evaluate(() => {
    const section = document.querySelector('aside');
    const link = Array.from(document.querySelectorAll('aside a[href*="maps/dir"]'))[0];
    return {
      found: !!link,
      href: link ? link.getAttribute('href') : null,
      overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
    };
  });
  results.push(`BOOKING id=1 [390] arahInReview=${booking.found} href=${booking.href ? booking.href.split('destination=')[1] : 'NULL'} overflow=${booking.overflow}`);

  // ---- no-location detail screenshot ----
  await goto(BASE + '/tenant/kos/4');
  await shot('gmaps_no_location_390.png');

  // ---- Desktop full pages ----
  await setViewport(VIEWPORTS[4]);
  await goto(BASE + '/tenant/kos/1');
  await shot('gmaps_desktop_detail_1280.png');
  await goto(BASE + '/tenant/kos/3');
  await shot('gmaps_desktop_fallback_1280.png');

  results.push('ERRORS: ' + (errors.length ? errors.join(' | ') : 'none'));
  console.log(JSON.stringify({ report, results }, null, 2));
  await browser.close();
})().catch((e) => { console.error('QA_FAILED', e.message); process.exit(1); });