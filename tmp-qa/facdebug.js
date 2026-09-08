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
  await page.goto(BASE + '/tenant/kos', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 800));

  const html = await page.evaluate(() => {
    const div = document.querySelector('div[x-data*="openFacility"]');
    return div ? div.outerHTML.slice(0, 700) : 'NOT FOUND';
  });
  console.log('=== facility x-data div ===\n' + html);

  // Also check the parent structure
  const parent = await page.evaluate(() => {
    const div = document.querySelector('div[x-data*="openFacility"]');
    if (!div) return 'none';
    let p = div.parentElement;
    let out = [];
    for (let i = 0; i < 6 && p; i++) {
      out.push(`<${p.tagName.toLowerCase()}> class="${(p.className || '').slice(0, 60)}" x-data="${(p.getAttribute('x-data') || '').slice(0, 50)}"`);
      p = p.parentElement;
    }
    return out;
  });
  console.log('\n=== ancestors ===\n' + parent.join('\n'));

  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });