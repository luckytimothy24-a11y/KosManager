const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 900 });
  await page.goto(BASE + '/login', { waitUntil: 'domcontentloaded' });
  await page.type('input[name="email"]', 'tenant@example.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('button[type="submit"]')]);
  await page.goto(BASE + '/dashboard', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 1000));

  // Dump all x-data attributes and their lengths
  const data = await page.evaluate(() => {
    const els = document.querySelectorAll('[x-data]');
    const list = [];
    els.forEach((e, i) => {
      list.push({ i, tag: e.tagName, attr: (e.getAttribute('x-data') || '').slice(0, 140) });
    });
    const promo = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    let promoHtml = promo ? promo.outerHTML.slice(0, 1800) : 'NOT FOUND';
    return { xdatas: list, promoHtml };
  });
  console.log('=== x-data attributes ===');
  data.xdatas.forEach(x => console.log(`[${x.i}] <${x.tag}> ${x.attr}`));
  console.log('\n=== promo section outerHTML (first 1800) ===\n' + data.promoHtml);
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });