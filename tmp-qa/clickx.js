const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

(async () => {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844 });
  page.on('console', m => { if (m.type() === 'error') console.log('CONSOLE-ERR:', m.text().slice(0, 120)); });
  await page.goto(BASE + '/login', { waitUntil: 'domcontentloaded' });
  await page.type('input[name="email"]', 'tenant@example.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('button[type="submit"]')]);
  await page.goto(BASE + '/dashboard', { waitUntil: 'domcontentloaded' });
  await new Promise(r => setTimeout(r, 900));

  const clickViaEvaluate = async () => {
    return page.evaluate(() => {
      const dots = [...document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button')];
      const target = dots[1];
      target.click();
      return new Promise(resolve => setTimeout(() => {
        const rail = document.querySelector('section[aria-label="Promo & Partner Pilihan"] div.flex.transition-transform');
        const d = [...document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button')];
        const active = d.findIndex(x => x.getAttribute('aria-current') === 'true');
        resolve({ transform: rail.style.transform, active });
      }, 300));
    });
  };

  console.log('element.click result:', JSON.stringify(await clickViaEvaluate()));
  console.log('page.click result:', await page.evaluate(() => {
    document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button')[1].click();
    return new Promise(resolve => setTimeout(() => {
      const rail = document.querySelector('section[aria-label="Promo & Partner Pilihan"] div.flex.transition-transform');
      const d = [...document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button')];
      const active = d.findIndex(x => x.getAttribute('aria-current') === 'true');
      resolve(JSON.stringify({ transform: rail.style.transform, active }));
    }, 300));
  }));
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });