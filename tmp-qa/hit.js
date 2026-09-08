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

  const hit = await page.evaluate(() => {
    const sec = document.querySelector('section[aria-label="Promo & Partner Pilihan"]');
    const dot = sec.querySelector('[role="tablist"] button');
    const r = dot.getBoundingClientRect();
    const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
    const el = document.elementFromPoint(cx, cy);
    const adSlide = sec.querySelector('.w-full.shrink-0 .group');
    const adR = adSlide.getBoundingClientRect();
    // find all elements potentially covering dots area
    const dotsRect = sec.querySelector('[role="tablist"]').getBoundingClientRect();
    const coverers = [];
    document.querySelectorAll('section[aria-label="Promo & Partner Pilihan"] *').forEach(e => {
      const er = e.getBoundingClientRect();
      if (e !== dot && e !== dot.parentElement && er.width > 0 && er.height > 0) {
        const overlap = er.left < cx && er.right > cx && er.top < cy && er.bottom > cy;
        if (overlap) coverers.push(e.tagName + '.' + (e.className || '').toString().slice(0, 60));
      }
    });
    return {
      dotRect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) },
      topElementAtDot: el ? el.tagName + '.' + (el.className || '').toString().slice(0, 60) : 'NONE',
      adSlideRect: { x: Math.round(adR.left), y: Math.round(adR.top), w: Math.round(adR.width), h: Math.round(adR.height) },
      coverersAtDot: coverers.slice(0, 8),
      pointerEvents: getComputedStyle(el).pointerEvents,
    };
  });
  console.log(JSON.stringify(hit, null, 1));
  await browser.close();
})().catch(e => { console.error('FAILED', e); process.exit(1); });