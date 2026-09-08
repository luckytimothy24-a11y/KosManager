const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844 });
  await page.goto(BASE + '/login', { waitUntil: 'domcontentloaded' });
  await page.type('input[name="email"]', 'tenant@example.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('button[type="submit"]')]);
  await page.goto(BASE + '/dashboard', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 900));

  const res = await page.evaluate(() => {
    window.Alpine = window.Alpine || { __version: undefined };
    const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    const root = sec.querySelector('div[x-data]');
    const dump = {};
    // try common accessors
    const x = root && root._x_dataStack && root._x_dataStack[0];
    dump.stackData = x ? { index: x.index, total: x.total, hasGo: typeof x.go } : 'no stack';
    dump.alpineVersion = window.Alpine && window.Alpine.version;
    dump.stateProp = root ? (root.state ? Object.keys(root.state).length : 'no .state') : 'null';
    dump.effect = root ? (root._x_effects && root._x_effects.length) : 'n/a';
    // manual click via dispatch
    return dump;
  });
  console.log(JSON.stringify(res));
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });