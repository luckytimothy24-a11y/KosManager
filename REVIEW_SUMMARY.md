# KosManager - Code Review Summary

## Project Overview
**KosManager** is a Laravel 10-based boarding house management system handling the full lifecycle: room search, booking, check-in, monthly billing, payment verification, and check-out. It has four roles: Super Admin, Owner, Admin, and Tenant.

---

## ✅ Yang Sudah Benar (Strengths)

### 1. State Machine Implementation
- Booking status transitions are rigorously enforced via `Booking::booted()` saving hook
- `canTransitionFrom()`, `getAllowedTransitions()`, and `transitionTo()` methods provide clear, server-side validation
- Terminal states properly blocked except legitimate transitions (e.g., `expired → cancelled`)
- Status constants defined centrally (`STATUS_PENDING`, `STATUS_APPROVED`, etc.)

### 2. Strong Security & Authorization
- **Policy-based authorization** on every record access (`BookingPolicy`, `UserPolicy`, etc.)
- **Role middleware** (`role:a,b`) groups routes by role before policy checks
- **Multi-owner isolation** properly implemented:
  - `User::canAccessKos()` checks `ownedKos` for owners, `assignedKos` pivot for admins
  - Cross-scope access returns 403 (`CheckInController` confirms this)
- **Tenant identity ownership**: tenant fills KTP/WA/address, owner only verifies
- **KTP masking**: `maskedIdentityNumber()` shows `************1234` to owner, never full number
- **Tenant cannot self-confirm**: endpoint `owner/check-in/{booking}/confirm` returns 403 for tenants via gate `identityPending()`

### 3. Comprehensive Audit Logging
- Audit logs cover login, CRUD, approve/reject actions
- **Anti-tamper protection**: each entry signed with HMAC-SHA256 using 64-hex secret
- Fail-closed: if secret invalid, entries are rejected
- Includes IP address for accountability

### 4. Transaction Safety & Race Condition Prevention
- `lockForUpdate` used in `CheckInController::process()` for both booking and kamar
- Double-payment protection: one active submission per tagihan
- Booking overlap protection: automatic rejection of conflicting periods
- Payment amount validation against total tagihan

### 5. Testing & Quality Assurance
- **47 passed / 174 assertions** in identity/security suite
- Separate test classes: `IdentityConfirmationTest`, `CheckInSecurityTest`, `CheckInOutTest`, `BusinessFlowTest`
- Full suite runs with PHPUnit + SQLite in-memory
- Code style validated via `vendor/bin/pint --test`
- CI/CD pipeline (GitHub Actions) runs tests on every push to `develop`

### 6. Payment Integrity
- Payment source of truth from `pembayarans` table, not booking alone
- `Pembayaran::ACTIVE_VERIFICATIONS` and `STATUS_APPROVED` constants
- Nominal pembayaran validated against total tagihan
- `hasApprovedPayment()` check as prerequisite for check-in

### 7. Code Structure & Maintainability
- All statuses defined as class constants
- Proper Eloquent casts (`date`, `decimal`, `datetime`)
- Accessors/mutators everywhere (`identityStatus()`, `paymentStatus()`, `nextAction()`, `priceUnitLabel()`)
- Service classes: `AuditLogService`, `NotificationService` decouple controllers from logic
- Helper methods on models reduce controller logic

### 8. Frontend & Build Setup
- Tailwind CSS + Alpine.js + Flowbite
- Vite build with 57 modules, 174.98 kB CSS
- Chart.js for dashboards
- Private storage for payment proofs (not publicly accessible)

---

## ⚠️ Yang Perlu Dibenahi (Areas for Improvement)

### 1. `BookingPolicy::reject()` Calls `approve()` - Logic Tightening Needed
**Issue**: `public function reject(User $user, Booking $booking)` simply calls `return $this->approve($user, $booking)`, which checks `$booking->status === Booking::STATUS_PENDING`. This means reject only works on pending bookings, but the method name suggests it could reject any booking.
**Problem**: If someone calls `reject()` on an `approved` booking, it silently fails (returns false) rather than providing a clear error message or allowing rejection of non-pending statuses.
**Fix**: Make `reject()` have its own logic or clarify documentation. Consider:
```php
public function reject(User $user, Booking $booking): bool
{
    return $this->isOwnerOrAdminOfKos($user, $booking)
        && in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED], true);
}
```

### 2. Tenant Route Middleware Redundancy
**Issue**: Tenant routes at line 359 have `'role:tenant', 'billing'` middleware:
```php
Route::middleware(['auth', 'role:tenant', 'billing'])->prefix('tenant')->name('tenant.')->group(function () {
```
The `billing` middleware is not defined in the standard Laravel Breeze setup and may cause issues or 403 errors if not properly registered/appplied.
**Fix**: Remove `'billing'` if not needed, or ensure it's defined in `app/Http/Middleware/BillingMiddleware.php`. Based on the code, the `billing` check may be redundant since tenant payment flow is already validated elsewhere.

### 3. `CheckInController::process()` - Transaction Method Length & Readability
**Issue**: The `process()` method is ~100 lines within a `@group \DB::transaction`, doing too many things:
- Scope validation
- Kamar availability check (shared vs personal)
- Penghuni/Kontrak/CheckIn creation
- Kamar status update based on occupancy
- Notifications and audit logs
**Fix**: Extract sub-methods like:
- `validateCheckInPrerequisites($booking)`
- `createTenantAndContract($booking, $data)`
- `updateKamarOccupancy($kamar, $penghuniCount)`
This would improve readability and testability.

### 4. Phone Number Normalization Edge Cases
**Issue**: In `TenantCheckInController::submit()`, phone normalization:
```php
if (str_starts_with($phone, '+62')) {
    $phone = '0'.substr($phone, 3);
} elseif (str_starts_with($phone, '62') && strlen($phone) >= 10) {
    $phone = '0'.substr($phone, 2);
}
```
If a user enters `628123456789` (9 digits after 62), the `strlen($phone) >= 10` check passes but the result may be wrong. Also, what if user enters `08...` already? The `str_starts_with($phone, '+62')` would fail, and `str_starts_with($phone, '62')` with >= 10 chars would prepend `0`, resulting in `008...` (double zero).
**Fix**: Add a check to ensure the phone doesn't already start with `0`:
```php
if (str_starts_with($phone, '+62')) {
    $phone = '0'.substr($phone, 3);
} elseif (!str_starts_with($phone, '0') && str_starts_with($phone, '62') && strlen($phone) >= 10) {
    $phone = '0'.substr($phone, 2);
}
```

### 5. Missing `billing` Middleware Definition
**Issue**: The `billing` middleware is referenced in `routes/web.php` line 359 but may not exist in `app/Http/Middleware/`. If it's not registered in `app/Http/Kernel.php`, it will cause a PHP error or fallback to guest middleware.
**Fix**: Either:
- Remove `'billing'` from the tenant route group if not needed
- Create `app/Http/Middleware/BillingMiddleware.php` and register it in `$middlewareGroups` or `$routeMiddleware`

### 6. `OwnerPolicy::isAdminScopedOwner()` Redundant Check
**Issue**: The private method checks `(int) $owner->id === (int) $admin->id` and returns false, but this is already handled by the earlier `if ($user->isSuperAdmin())` and `if ($user->isAdmin())` checks in the same method. The redundancy isn't harmful but adds unnecessary complexity.
**Fix**: Simplify the method by removing the id comparison since super admin check already happened above.

### 7. `BookingPolicy::approve()` Should Also Verify Payment Prerequisite (Per Doc)
**Issue**: The docstring says: "Prerequisite payment dicek di BookingStatusService (payment_required)." But the policy only checks `status === STATUS_PENDING`. Payment verification may happen elsewhere, but the policy should ideally also check `hasApprovedPayment()` or at least document that it's handled in the service layer.
**Fix**: Either add the check to the policy or add a clear comment that payment verification is handled in `BookingStatusService`, not the policy.

### 8. Some Controllers Lack Explicit Rate Limiting
**Issue**: Several POST endpoints for identity/submit don't have throttle middleware in the routes:
- `Route::post('checkin/{booking}/identity', [TenantCheckInController::class, 'submit'])` - has `throttle:10,60` (line 376) ✓
- But other identity-related endpoints may be missing
**Fix**: Ensure all sensitive POST endpoints have rate limiting, especially identity submission and payment verification.

### 9. `Kamar::occupancy()` Method Should Handle Edge Cases
**Issue**: The occupancy check in `CheckInController::process()` line 210-216:
```php
$freshOccupancy = Penghuni::where('kamar_id', $kamar->id)->where('status', 'active')->count();
if ($freshOccupancy >= (int) $kamar->capacity) {
    $kamar->update(['status' => 'occupied']);
} elseif ($freshOccupancy === 0) {
    $kamar->update(['status' => 'booked']);
} else {
    $kamar->update(['status' => 'available']);
}
```
If `$kamar->capacity` is null/0, this could cause issues or unexpected behavior.
**Fix**: Add a default: `->where('status', 'active')::whereNotNull('capacity')` or default capacity of 1.

### 10. Advertising Tables - Extra Complexity Not Core to Kos Management
**Issue**: The project includes advertising campaign/order/event tables that are not core to the kos boarding house management functionality. While not necessarily a bug, they add database complexity and may distract from the core feature set.
**Fix**: If this is intended as a kos-only management system, consider separating advertising into a separate package or removing if not used.

---

## 📊 Prioritas Perbaikan

| Priority | Issue | Impact | Action |
|----------|-------|--------|--------|
| **Kritis** | `billing` middleware potentially missing/undefined | Could break tenant routes or cause 500 errors | Define middleware or remove from routes |
| **Sedang** | `BookingPolicy::reject()` delegates to `approve()` - unclear semantics | Potential bugs when rejecting non-pending bookings | Refactor `reject()` with proper status check |
| **Sedang** | `CheckInController::process()` method too long in single transaction | Reduced readability, harder to maintain/debug | Extract sub-methods |
| **Opsional** | Phone number normalization edge cases | Could produce invalid numbers (double `0`) | Add guard clause for existing `0` prefix |
| **Opsional** | Audit log anti-tamper secret not rotated/rotated securely | Security best practice - ensure secret is rotated | Document secret management procedure |

---

## 🚀 Langkah Selanjutnya (Next 3-5 Actions)

1. **Fix the `billing` middleware issue** - Either create `app/Http/Middleware/BillingMiddleware.php` and register it, or remove `'billing'` from tenant route group at `routes/web.php:359`. This is critical to prevent route errors.

2. **Refactor `BookingPolicy::reject()`** - Give it independent logic instead of delegating to `approve()`. At minimum, allow rejection of both `pending` and `approved` statuses with clear behavior.

3. **Extract `CheckInController::process()` sub-methods** - Split the transaction into:
   - `validateCheckInPrerequisites()`
   - `createTenantContractCheckIn()`
   - `updateKamarStatusAfterCheckIn()`
   This improves readability and makes each piece testable in isolation.

4. **Fix phone number normalization** - Add guard `!str_starts_with($phone, '0')` before the `62` check in `TenantCheckInController::submit()` to prevent `008...` issue.

5. **Run full test suite** after fixes: `php artisan test` + `vendor/bin/pint --test` + `npm run build`. Ensure all 47 identity/security tests still pass.

---
*Review based on codebase examination as of October 2026. Project shows strong architectural decisions (state machine, identity ownership, policy authorization, audit logging with anti-tamper) with minor code quality and edge case improvements needed.*