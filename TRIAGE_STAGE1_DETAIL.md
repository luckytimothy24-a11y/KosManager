# TRIAGE STAGE 1 PROMO-ART CAROUSEL — LAPORAN DETAIL READ-ONLY

**Status:** 7 butir triage, 3 test failures dikonfirmasi, root cause teridentifikasi.

---

## 1. STATUS STAGE 1 — UNCOMMITTED & UNTRACKED

### git status output:
```
+ Staged: 1 files (unrelated: database/migrations/2026_10_28_000001_add_recipient_snapshot_to_payouts_table.php)
~ Modified: 169 files (Stage 1 components termasuk di dalamnya)
? Untracked: 81 files (termasuk promo-art SVG components dan 2 migration files Stage 1)
```

### git branch -v output:
```
* feature/alur-dana-2-tahap      5bb4168 Fix: Perbaiki error 500 saat login - hapus duplikasi kode dan syntax error di sidebar.blade.php
```

### Analisis:
- **HEAD:** 5bb4168 (login fix, 2026-11-04)
- **Commit sebelum Stage 1 signifikan:** 9d86ae4 `snapshot: perbaikan view, fasilitas, tes readiness` (2026-10-21)
- **Commit dengan perubahan partner-ad.blade.php pertama kali:** 552c808 `feat: enhance monetization and tenant experience` (2026-09-08)
- **Stage 1 file status:**
  - `resources/views/components/ads-carousel.blade.php` — Modified (dari 9d86ae4, stage 1)
  - `resources/views/components/promo-art/` — Untracked (NEW, stage 1)
  - `app/Models/AdvertisingCampaign.php` — Modified (stage 1)
  - `app/Services/AdvertisingService.php` — Modified (stage 1)
  - `database/migrations/2026_11_03_000006_add_highlight_text_to_advertising_campaigns_table.php` — Untracked (stage 1)
  - `database/migrations/2026_11_03_000007_add_visual_theme_to_advertising_campaigns_table.php` — Untracked (stage 1)
  - `resources/css/app.css` — Modified (stage 1)

### **Output:** Status Stage 1: ⚠️ UNCOMMITTED & UNTRACKED — semua perubahan stage 1 ada di working tree, belum di-commit

---

## 2. FAILURE: MOTION-SAFE TRANSLATE-X MISMATCH (Line 185 vs 214)

### Test failure:
```
FAIL  test_component_uses_reduced_motion_safe_transitions

Failed asserting that [...card HTML...] contains "motion-safe:group-hover:translate-x-0.5"
at tests/Feature/PartnerAdRedesignTest.php:221
```

### git log -S"translate-x-1" history:
```
commit 552c8089af650a753b7850d64c0bdec4ad48c553 (2026-09-08)
Subject: feat: enhance monetization and tenant experience
```

### Perubahan dari commit 552c808 → current working tree:

**Commit 552c808** (line 223):
```blade
<i class="ri-arrow-right-line transition-transform motion-safe:group-hover:translate-x-0.5"></i>
```

**Current working tree** (line 185):
```blade
<span class="inline-flex min-h-[44px] items-center gap-1.5 rounded-2xl {{ $theme['cta'] }} text-white text-xs font-bold px-5 shadow-md transition-all duration-200 active:scale-95 group-hover:shadow-lg">
    {{ $cta }} <i class="ri-arrow-right-line transition-transform motion-safe:group-hover:translate-x-1"></i>
```

**Perbedaan:**
- **Banner** (line 143): `motion-safe:group-hover:translate-x-1` ✅ SESUAI test expectation (line 220)
- **Card** (line 185): `motion-safe:group-hover:translate-x-1` ❌ Test expects `translate-x-0.5` (line 221)

### Template fixture (test):
```php
// tests/Feature/PartnerAdRedesignTest.php:213-222
public function test_component_uses_reduced_motion_safe_transitions(): void
{
    $ad = $this->livePartnerCampaign('dana', 'marketplace');

    $banner = $this->renderAd($ad, 'marketplace', 'banner');
    $card = $this->renderAd($ad, 'marketplace', 'card');

    $this->assertStringContainsString('motion-safe:group-hover:translate-x-1', $banner);
    $this->assertStringContainsString('motion-safe:group-hover:translate-x-0.5', $card);  // FAIL
}
```

### **Output:** ❌ MASALAH — Card variant menggunakan `translate-x-1` (seperti banner), test mengharapkan `translate-x-0.5` (lebih subtle untuk card)

**Root cause:** Stage 1 refactor menyamakan semua translate-x menjadi 1, inconsistent dengan design intent banner vs card.

---

## 3. FAILURE: MONOGRAM & LOGO EKSTERNAL PRIORITY (Lines 83-91 & 18)

### Test failures:
```
FAIL  test_banner_without_logo_or_image_falls_back_to_typographic_monogram
  Expected: NO <img tag
  Actual: <img src="/images/partners/dana.svg" alt="" ...> rendering

FAIL  test_campaign_with_logo_renders_registered_partner_logo
  Expected: https://cdn.example.com/shopee.png
  Actual: /images/partners/shopee.svg (official logo override)
```

### Template logic (partner-ad.blade.php:1-91):
```php
// Line 18: External logo dari database atau advertiser_logo
$brandLogo = $ad->partner && $ad->partner->logo ? $ad->partner->logo : ($ad->advertiser_logo ?: null);

// Line 83-91: Official logo registry OVERRIDES external logo
$partnerLogos = [
    'dana' => asset('images/partners/dana.svg'),
    'shopee' => asset('images/partners/shopee.svg'),
    'gopay' => asset('images/partners/gopay.svg'),
];
$officialLogo = $partnerLogos[$partnerSlug] ?? null;
if ($officialLogo) {
    $brandLogo = $officialLogo;  // <-- OVERWRITES external logo!
}
```

### Test fixture 1 — Monogram fallback (test line 149-166):
```php
public function test_banner_without_logo_or_image_falls_back_to_typographic_monogram(): void
{
    $ad = $this->livePartnerCampaign('dana', 'marketplace');  // NO explicit logo set
    $html = $this->renderAd($ad, 'marketplace', 'banner');

    // Expectation: monogram dengan initial 'D', TANPA <img>
    $this->assertStringContainsString('aria-hidden="true">D', $html);
    $this->assertStringNotContainsString('<img', $html);  // FAIL: <img found!
}
```

**Actual HTML rendered:**
```html
<span class="relative grid ...">
  <span aria-hidden="true">D</span>
  <img src="/images/partners/dana.svg" alt="" loading="lazy" ...>  <!-- RENDERED! -->
</span>
```

### Test fixture 2 — External logo (test line 168-176):
```php
public function test_campaign_with_logo_renders_registered_partner_logo(): void
{
    $ad = $this->livePartnerCampaign('shopee', 'homepage');
    $ad->partner->update(['logo' => 'https://cdn.example.com/shopee.png']);  // EXTERNAL logo

    $html = $this->renderAd($ad->fresh(), 'homepage', 'banner');

    $this->assertStringContainsString('https://cdn.example.com/shopee.png', $html);  // FAIL: NOT found!
}
```

**Actual HTML rendered:**
```html
<img src="/images/partners/shopee.svg" alt="" ...>  <!-- OFFICIAL override! -->
```

### **Output:** ❌ MASALAH — Official logo registry (lines 83-91) mem-OVERRIDE external logo dan advertiser_logo, mengakibatkan:
1. Monogram tidak bisa jadi fallback tunggal (akan ada <img overlay)
2. External logo di database tidak pernah dipakai (priority salah)

**Design intent (dari test):**
- Jika ada external logo (partner.logo atau advertiser_logo) → gunakan
- Jika tidak → fallback ke monogram inisial (TANPA <img>, hanya gradient bg + huruf)

**Current behavior:**
- Official registry > external logo > monogram (WRONG priority)

---

## 4. JS LIFECYCLE STAGE 1 — ADS-CAROUSEL & PROMO-ART

### File: `resources/views/components/ads-carousel.blade.php`

Pencarian: `is-playing`, `visibilitychange`, `IntersectionObserver`, `setInterval`, `requestAnimationFrame`, `clearInterval`, `cancelAnimationFrame`, `matchMedia`

**Alpine x-data scope (dari current working tree):**
```blade
x-data="{
    slide: 0,
    timer: null,
    totalSlides: {{ $ads->count() }},
    paused: false,
    windowWidth: window.innerWidth,
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    init() { ... },
    destroy() { clearInterval(this.timer); },
    startTimer() { this.timer = setInterval(() => { this.slide = (this.slide + 1) % this.totalSlides; }, 5000); },
    ...
}"
```

**Lifecycle methods:**
- `init()` (line ~16-21): 
  - `matchMedia('(prefers-reduced-motion: reduce)').matches` — guard reduced-motion ✅
  - Inisialisasi timer jika NOT reduced-motion ✅
  - Window resize listener ✅
  
- `destroy()` (line 22): 
  - `clearInterval(this.timer)` — cleanup ✅
  
- `startTimer()` (line 24): 
  - `setInterval()` setiap 5000ms
  
- `resetTimer()` (line ~26):
  - `clearInterval(this.timer)` sebelum inisialisasi ulang ✅

**Potential issues:**
- `destroy()` tidak dipanggil otomatis pada Alpine (hanya manual via `@destroy` atau saat element dihapus)
- Multiple `setInterval` bisa berjalan ganda jika `resetTimer()` tidak clear sebelum start baru
- Visibility/tab switching: TIDAK ada `visibilitychange` listener — carousel terus berjalan di background

### **Output:** ⚠️ OK (PARTIAL) — Lifecycle ada, reduced-motion guard ada, tapi:
- Background carousel berjalan terus (no visibilitychange handler)
- destroy() tidak terikat otomatis ke Alpine lifecycle
- Tim testing harus verify no duplicate timers saat slide berganti cepat

---

## 5. DARK MODE CSS VARIABLES

### File: `resources/css/app.css` & `resources/views/tenant/partials/partner-art.blade.php`

**Search for --promo-from, --promo-to, --promo-accent:**

Tidak ditemukan CSS variable global dengan nama tersebut. Sebaliknya, theme menggunakan **hardcoded Tailwind colors per partner**.

### Dari partner-ad.blade.php (lines 26-79):
```php
$themes = [
    'dana' => [
        'grad' => 'bg-gradient-to-br from-[#eaf4ff] via-white to-[#d3e9ff] dark:from-[#081d36] dark:via-[#0b2a4d] dark:to-[#081d36]',
        ...
    ],
    'shopee' => [
        'grad' => 'bg-gradient-to-br from-[#fff3ee] via-white to-[#ffe2d5] dark:from-[#27120e] dark:via-[#3a1c13] dark:to-[#27120e]',
        ...
    ],
    'gopay' => [
        'grad' => 'bg-gradient-to-br from-[#eefbf1] via-white to-[#d5f4de] dark:from-[#0a2417] dark:via-[#0d3120] dark:to-[#0a2417]',
        ...
    ],
    'default' => [
        'grad' => 'bg-gradient-to-br from-slate-50 via-white to-primary-50 dark:from-slate-700 dark:via-surface-elevated dark:to-slate-800',
        ...
    ],
];
```

**Dark mode colors per theme:**
- **DANA dark:** `from-[#081d36]` (blue), `via-[#0b2a4d]`, `to-[#081d36]`
- **Shopee dark:** `from-[#27120e]` (brown), `via-[#3a1c13]`, `to-[#27120e]`
- **GoPay dark:** `from-[#0a2417]` (green), `via-[#0d3120]`, `to-[#0a2417]`
- **Default dark:** `from-slate-700`, `via-surface-elevated`, `to-slate-800`

### **Output:** ✅ OK — Dark mode hardcoded per tema (tidak via CSS variable), setiap brand punya warna dark spesifik.

---

## 6. FALLBACK VISUAL_THEME — BLADE LOGIC

### File: `resources/views/components/ads-carousel.blade.php` & `resources/views/tenant/partials/partner-ad.blade.php`

**Saat visual_theme null atau tidak dikenal:**

Dari partner-ad.blade.php line 81:
```php
$theme = $themes[$partnerSlug] ?? $themes['default'];
```

- Jika `$partnerSlug` tidak ada di `$themes` array → fallback ke `'default'` ✅
- Default theme menggunakan primary color netral (indigo)

### **Output:** ✅ OK — Fallback ke 'default' theme dengan gradient primary-based, aman jika visual_theme tidak diketahui.

---

## 7. TEST COVERAGE STAGE 1

### File: `tests/Feature/PartnerAdRedesignTest.php`

**Tests yang ada (37 test dalam suite):**

#### A. Rendering dasar (lines 55-108):
- ✅ `test_banner_renders_sponsored_badge_brand_headline_desc_cta_and_tracking_url` (line 55)
- ✅ `test_card_variant_renders_compact_premium_markup` (line 77)
- ✅ `test_compact_variant_renders_slim_row_with_full_touch_target` (line 93)

#### B. Tema visual per partner (lines 114-143):
- ✅ `test_partner_specific_visual_identity_dana_shopee_gopay` (line 114)
- ✅ `test_unknown_partner_uses_neutral_default_theme` (line 135)

#### C. Fallback aman (lines 149-176):
- ❌ `test_banner_without_logo_or_image_falls_back_to_typographic_monogram` (line 149) — REGRESSION
- ❌ `test_campaign_with_logo_renders_registered_partner_logo` (line 168) — REGRESSION

#### D. Keamanan & aksesibilitas (lines 182-195):
- ✅ `test_ad_link_never_sets_target_blank_and_has_accessible_name` (line 182)

#### E. Responsif (lines 202-222):
- ✅ `test_banner_image_hidden_on_mobile_shown_on_md_plus` (line 202)
- ❌ `test_component_uses_reduced_motion_safe_transitions` (line 213) — REGRESSION

#### F. Integrasi halaman & tracking (lines 228-282):
- ✅ `test_dashboard_marketplace_detail_render_premium_sections` (line 228)
- ✅ `test_clicking_rendered_ad_records_event_and_redirects_to_destination` (line 262)

#### G. Regresi browser QA (lines 289-336):
- ✅ `test_dashboard_promo_carousel_has_alpine_root_scope` (line 289)
- ✅ `test_marketplace_facility_accordion_xdata_uses_quoted_json_values` (line 318)

**Tests belum ada untuk Stage 1:**
- ❌ visual_theme column data persistence
- ❌ AdvertisingCampaign::inferVisualTheme() logic
- ❌ Promo-art SVG components rendering (shopping, payment, wallet, generic)
- ❌ Dark mode CSS variables per visual_theme
- ❌ wallet counter animation lifecycle (visibility, reduced-motion, reset)
- ❌ highlight_text column rendering
- ❌ IntersectionObserver untuk wallet counter animation

### **Output:** ⚠️ TEST COVERAGE BELUM LENGKAP — 34 tests ada, 3 failures (regresi), 6+ critical tests MISSING untuk stage 1 completeness

---

## RINGKASAN — SUMMARY

| Butir | Status | Bukti |
|-------|--------|-------|
| 1. Status Stage 1 | ⚠️ UNCOMMITTED | 169 modified, 81 untracked, ads-carousel.blade.php modified, promo-art/ untracked |
| 2. Motion-safe translate-x | ❌ MASALAH | Card uses translate-x-1, test expects translate-x-0.5 (line 185 vs 214) |
| 3. Monogram & logo eksternal | ❌ MASALAH | Official logo registry overrides external logo, monogram test fails (lines 83-91) |
| 4. JS lifecycle | ⚠️ OK (PARTIAL) | Cleanup ada, reduced-motion guard ada, tapi no visibilitychange handler |
| 5. Dark mode | ✅ OK | Hardcoded per tema, dana/shopee/gopay/default punya dark colors |
| 6. Fallback tema | ✅ OK | $theme = $themes[$partnerSlug] ?? $themes['default'] (line 81) |
| 7. Test coverage | ⚠️ BELUM LENGKAP | 34 tests ada, 3 failures, 6+ critical missing untuk stage 1 |

---

## REKOMENDASI LANJUTAN

1. **Motion-safe:** Ubah card variant line 185 dari `translate-x-1` → `translate-x-0.5` (lebih subtle)
2. **Logo priority:** Hapus official logo registry override (lines 83-91), biarkan external logo atau advertiser_logo jadi priority, monogram fallback last
3. **JS lifecycle:** Tambah `visibilitychange` listener di carousel untuk pause saat tab hidden
4. **Test coverage:** Tambah tests untuk visual_theme persistence, SVG rendering, wallet counter lifecycle

**Blocked:** Tunggu keputusan user untuk proceed dengan fix atau lanjut triage deeper.
