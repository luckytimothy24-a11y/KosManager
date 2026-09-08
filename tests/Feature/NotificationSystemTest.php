<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    private function notification(User $user, ?array $data = null): Notification
    {
        return Notification::factory()->create([
            'user_id' => $user->id,
            'data' => $data,
        ]);
    }

    // ---------------------------------------------------------------- UI / inbox

    public function test_user_can_open_own_notifications_inbox(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $this->notification($user);
        $this->notification($user);
        Notification::factory()->create(['user_id' => $user->id, 'is_read' => true]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('notifications', function (LengthAwarePaginator $p) {
            return $p->total() === 3 && $p->perPage() === 15;
        });
        $response->assertSee('Tandai Semua Dibaca');
        $response->assertSee('Tandai Dibaca');
    }

    public function test_inbox_only_lists_own_notifications_tenant_isolation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $noteA = $this->notification($userA);

        $response = $this->actingAs($userB)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertDontSee($noteA->title);
        $this->assertSame(1, Notification::forUser($userA)->count());
        $this->assertSame(0, Notification::forUser($userB)->count());
    }

    public function test_inbox_paginates_at_15(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 20; $i++) {
            Notification::factory()->create(['user_id' => $user->id, 'title' => "Notif {$i}"]);
        }

        $firstPage = $this->actingAs($user)->get(route('notifications.index'));
        $firstPage->assertOk();
        $this->assertSame(20, $firstPage->viewData('notifications')->total());
        $this->assertSame(15, $firstPage->viewData('notifications')->perPage());

        $secondPage = $this->actingAs($user)->get(route('notifications.index', ['page' => 2]));
        $secondPage->assertOk();
        $this->assertSame(5, $secondPage->viewData('notifications')->count());
    }

    public function test_inbox_shows_empty_state_without_notifications(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Belum ada notifikasi');
    }

    // ---------------------------------------------------------------- state

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create();
        $note = $this->notification($user);

        $response = $this->actingAs($user)->from(route('notifications.index'))
            ->post(route('notifications.read', $note));

        $response->assertRedirect(route('notifications.index'));
        $this->assertTrue($note->fresh()->is_read);
        $this->assertSame(0, Notification::unread()->forUser($user)->count());
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $noteOfB = $this->notification($userB);

        $response = $this->actingAs($userA)->post(route('notifications.read', $noteOfB));

        $response->assertForbidden();
        $this->assertFalse($noteOfB->fresh()->is_read);
    }

    public function test_mark_all_read_only_updates_own_notifications(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $this->notification($userA);
        $noteOfB = $this->notification($userB);

        $response = $this->actingAs($userA)->post(route('notifications.markAllRead'));

        $response->assertRedirect();
        $this->assertSame(0, Notification::unread()->forUser($userA)->count());
        $this->assertSame(1, Notification::unread()->forUser($userB)->count());
        $this->assertFalse($noteOfB->fresh()->is_read);
    }

    // ---------------------------------------------------------------- duplicate prevention

    public function test_create_with_same_unique_key_is_deduplicated(): void
    {
        $user = User::factory()->create();

        $first = NotificationService::create($user->id, 'billing', 'Title', 'Message', null, 'bill-due:123');
        $second = NotificationService::create($user->id, 'billing', 'Title', 'Message', null, 'bill-due:123');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Notification::where('user_id', $user->id)->count());
    }

    public function test_create_with_different_unique_key_creates_separate_rows(): void
    {
        $user = User::factory()->create();

        NotificationService::create($user->id, 'billing', 'Title', 'Message', null, 'bill-due:1');
        NotificationService::create($user->id, 'billing', 'Title', 'Message', null, 'bill-due:2');

        $this->assertSame(2, Notification::where('user_id', $user->id)->count());
    }

    public function test_duplicate_key_does_not_leak_between_users(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        NotificationService::create($a->id, 'billing', 'Title', 'Message', null, 'bill-due:99');
        NotificationService::create($b->id, 'billing', 'Title', 'Message', null, 'bill-due:99');

        $this->assertSame(1, Notification::forUser($a)->count());
        $this->assertSame(1, Notification::forUser($b)->count());
    }

    public function test_reminder_command_does_not_duplicate_notifications(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->artisan('tagihan:remind-due-soon', ['--days' => 3])->assertSuccessful();
        $this->artisan('tagihan:remind-due-soon', ['--days' => 3])->assertSuccessful();

        $this->assertSame(1, Notification::forUser($tenant)->where('type', 'billing')->where('title', 'Pengingat Jatuh Tempo')->count());
    }

    // ---------------------------------------------------------------- booking & payment events

    public function test_booking_store_notifies_tenant_and_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'rental_type' => 'daily',
            'notes' => null,
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Notification::forUser($tenant)->where('type', NotificationType::BOOKING)->where('title', 'Booking Berhasil')->count());
        $this->assertSame(1, Notification::forUser($owner)->where('type', NotificationType::BOOKING)->where('title', 'Booking Baru')->count());
    }

    public function test_cash_payment_submitted_notifies_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan->id,
            'amount' => $tagihan->total,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Notification::forUser($owner)->where('type', NotificationType::PAYMENT)->where('title', 'Pembayaran Baru')->count());
    }

    public function test_webhook_verified_payment_notifies_tenant(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $payment = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => $tagihan->total,
            'payment_method' => 'e_wallet',
            'verification_status' => 'pending',
            'gateway_provider' => 'sandbox',
            'gateway_reference' => 'VA-'.strtoupper(Str::random(6)),
        ]);
        $tagihan->update(['status' => 'pending_verification']);

        $payload = [
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'success',
            'amount' => (float) $payment->amount,
        ];
        $signature = hash_hmac('sha256', json_encode($payload), config('payment-gateway.signature_key'));

        $response = $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => $signature,
        ]);

        $response->assertOk();
        $this->assertSame(1, Notification::forUser($tenant)->where('type', NotificationType::PAYMENT)->where('title', 'Pembayaran Diverifikasi')->count());
    }

    // ---------------------------------------------------------------- support

    public function test_notification_type_labels(): void
    {
        $this->assertSame('Booking', NotificationType::label(NotificationType::BOOKING));
        $this->assertSame('Tagihan', NotificationType::label(NotificationType::BILLING));
        $this->assertSame('Lainnya', NotificationType::label('unknown'));
        $this->assertArrayHasKey(NotificationType::PAYMENT, NotificationType::all());
    }
}
