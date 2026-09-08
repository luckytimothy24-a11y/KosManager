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
  await new Promise(r => setTimeout(r, 800));

  const state = async (label) => {
    const s = await page.evaluate(() => {
      const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
      const rail = sec.querySelector('[\\:style]') || sec.querySelector('div[style*="transform"]');
      const dots = sec.querySelectorAll('[role="tablist"] button');
      let active = -1;
      dots.forEach((d, i) => { if (d.getAttribute('aria-current') === 'true') active = i; });
      return {
        transform: rail ? rail.style.transform : 'none',
        activeDot: active,
        dotsWidths: [...dots].map(d => d.offsetWidth),
      };
    });
    console.log(`[${label}]`, JSON.stringify(s));
  };

  await state('initial');
  // click the dots (mobile-visible control)
  const dots = await page.$$('section[aria-label="Promo & Partner Pilihan"] [role="tablist"] button');
  if (dots.length >= 2) { await dots[1].click(); }
  await new Promise(r => setTimeout(r, 900));
  await state('after click dots[1]');

  await page.screenshot({ path: 'C:/Users/ASUS/KosManager/tmp-qa/proof_carousel_slide2_390.png' });

  // next arrow (desktop) via click even if hidden
  try {
    await page.click('section[aria-label="Promo & Partner Pilihan"] button[aria-label="Slide berikutnya"]');
  } catch (e) { console.log('arrow click failed:', e.message.slice(0, 60)); }
  await new Promise(r => setTimeout(r, 900));
  await state('after next arrow');

  await page.screenshot({ path: 'C:/Users/ASUS/KosManager/tmp-qa/proof_carousel_slide3_390.png' });

  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });