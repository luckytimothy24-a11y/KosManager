const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 900 });

  page.on('pageerror', (e) => {
    console.log('\nPAGEERROR:', e.message);
    if (e.stack) {
      console.log('STACK:', e.stack.split('\n').slice(0, 6).join(' | '));
    }
  });

  await page.goto(BASE + '/login', { waitUntil: 'domcontentloaded' });
  await page.type('input[name="email"]', 'tenant@example.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('button[type="submit"]')]);

  console.log('\n===== MARKETPLACE =====');
  await page.goto(BASE + '/tenant/kos', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 1200));
  // click the filter button to open drawer
  try {
    await page.click('button:has-text("Filter")');
  } catch (e) {
    const btns = await page.$$('button');
    for (const b of btns) { const t = await b.evaluate(el => el.innerText); if (t.includes('Filter')) { await b.click(); break; } }
  }
  await new Promise(r => setTimeout(r, 800));

  const facMeta = await page.evaluate(() => {
    const drawer = document.querySelector('[x-data="{ filterOpen: false }"]');
    return {
      rootHasAlpine: !!drawer,
      dataAttr: drawer ? (drawer.getAttribute('x-data') || '') : 'N/A',
      facilityDivs: document.querySelectorAll('div[x-data*="openFacility"]').length,
      facilityCategoryCount: document.querySelectorAll('button[type="button"][x-on\\:click]').length,
    };
  });
  console.log('facility meta:', JSON.stringify(facMeta));

  console.log('\n===== DASHBOARD (after carousel fix) =====');
  await page.goto(BASE + '/dashboard', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 1200));
  const promo = await page.evaluate(() => {
    const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    const el = sec ? sec.querySelector('div[x-data]') : null;
    return {
      found: !!el,
      attr: el ? (el.getAttribute('x-data') || '').slice(0, 60) : 'N/A',
      dots: sec ? sec.querySelectorAll('[role="tablist"] button').length : 0,
      arrows: sec ? sec.querySelectorAll('button[aria-label]').length : 0,
      slideWidth: sec ? sec.querySelector('.w-full.shrink-0')?.offsetWidth : 0,
      railTransform: sec ? sec.querySelector('[\\:style]')?.style.transform : 'N/A',
    };
  });
  console.log('carousel after fix:', JSON.stringify(promo));

  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });