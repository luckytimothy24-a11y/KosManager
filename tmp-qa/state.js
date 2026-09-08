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

  const out = await page.evaluate(() => {
    const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    const root = sec.querySelector('div[x-data]');
    const state = root ? root.__x : null;
    let alpineData = null;
    try {
      const proxy = state && state.$data;
      alpineData = { index: proxy ? proxy.index : 'n/a', total: proxy ? proxy.total : 'n/a' };
    } catch (e) { alpineData = 'err ' + e.message; }

    const dots = sec.querySelectorAll('[role="tablist"] button');
    const dot0 = dots[0], dot1 = dots[1];
    return {
      rootAttr: root ? (root.getAttribute('x-data') || '').slice(0, 40) : 'NULL',
      hasXeffect: root ? dirAttr(root, ':effect') || dirAttr(root, 'x-effect') : 'n/a',
      alpineData,
      dot1ClickAttr: dot1 ? dot1.getAttribute('@click') || dot1.getAttribute('x-on:click') || dot1.getAttribute('x-on\\:click') : 'none',
      dot1Outer: dot1 ? dot1.outerHTML.slice(0, 200) : 'none',
      secHeaderKeys: Object.keys(sec.querySelector('.mb-3')).slice(0,0),
      railCls: sec.querySelector('div.flex.transition-transform') ? 'rail-found' : 'rail-missing',
      railChildren: sec.querySelectorAll('.w-full.shrink-0').length,
    };
    function dirAttr(el, name) { if (el.hasAttribute(name)) return el.getAttribute(name); }
  });
  console.log(JSON.stringify(out, null, 1));
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });