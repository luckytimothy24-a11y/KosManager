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
    const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    const root = sec.querySelector('div[x-data]');
    const stack = root._x_dataStack[0];
    const results = [];
    const before = stack.index;
    stack.go(1);
    results.push('go(1) called, before=' + before + ' after=' + stack.index);
    // let transitions settle in the same task via microtask chain
    return new Promise(resolve => setTimeout(() => {
      const rail = sec.querySelector('div.flex.transition-transform');
      const dot1 = sec.querySelectorAll('[role="tablist"] button')[1];
      results.push('transform=' + (rail.style.transform || 'nil') + ' activeDot=' + [...sec.querySelectorAll('[role="tablist"] button')].findIndex(d => d.getAttribute('aria-current') === 'true'));
      resolve(results);
    }, 50));
  });
  console.log(JSON.stringify(res));
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });