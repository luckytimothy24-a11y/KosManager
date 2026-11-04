<?php

namespace Tests\Feature\Tenant;

use App\Models\MarketplaceItem;
use App\Models\MarketplaceReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceDuplicateReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure migrations are run for SQLite in-memory
        $this->artisan('migrate', ['--env' => 'testing']);
    }

    public function test_user_can_report_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'spam',
            'description' => 'This is spam',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');

        $this->assertDatabaseHas('marketplace_reports', [
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'This is spam',
            'status' => 'pending',
        ]);
    }

    public function test_reporter_cannot_create_second_pending_report_for_same_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // First report
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'First report',
            'status' => 'pending',
        ]);

        // Second attempt via controller
        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'penipuan',
            'description' => 'Second attempt',
        ]);

        // Should be blocked with user-friendly error
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda sudah memiliki laporan pending untuk item ini.');

        // Only one report should exist
        $this->assertDatabaseCount('marketplace_reports', 1);
    }

    public function test_reporter_can_report_different_items(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item1 = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Item 1',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);
        $item2 = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Item 2',
            'description' => 'Description',
            'price' => 200000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // Report item1
        MarketplaceReport::create([
            'item_id' => $item1->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'Report item 1',
            'status' => 'pending',
        ]);

        // Report item2 - should be allowed
        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item2), [
            'reason' => 'penipuan',
            'description' => 'Report item 2',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');

        $this->assertDatabaseCount('marketplace_reports', 2);
    }

    public function test_different_reporters_can_report_same_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter1 = User::factory()->create(['role' => 'tenant']);
        $reporter2 = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // Reporter 1 reports
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter1->id,
            'reason' => 'spam',
            'description' => 'Report from reporter 1',
            'status' => 'pending',
        ]);

        // Reporter 2 reports same item - should be allowed
        $this->actingAs($reporter2);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'penipuan',
            'description' => 'Report from reporter 2',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');

        $this->assertDatabaseCount('marketplace_reports', 2);
    }

    public function test_reporter_can_report_again_after_previous_dismissed(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // First report - dismissed by moderator
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'First report',
            'status' => 'dismissed',
        ]);

        // Second report after dismissal - should be allowed
        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'penipuan',
            'description' => 'Second report after dismissal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');

        // Both reports should exist
        $this->assertDatabaseCount('marketplace_reports', 2);
        $this->assertDatabaseHas('marketplace_reports', [
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'status' => 'dismissed',
        ]);
        $this->assertDatabaseHas('marketplace_reports', [
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'status' => 'pending',
        ]);
    }

    public function test_reporter_can_report_again_after_previous_reviewed(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // First report - reviewed by moderator
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'First report',
            'status' => 'reviewed',
        ]);

        // Second report after review - should be allowed
        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'penipuan',
            'description' => 'Second report after review',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Laporan berhasil dikirim. Tim kami akan meninjau.');

        // Both reports should exist
        $this->assertDatabaseCount('marketplace_reports', 2);
        $this->assertDatabaseHas('marketplace_reports', [
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'status' => 'reviewed',
        ]);
        $this->assertDatabaseHas('marketplace_reports', [
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'status' => 'pending',
        ]);
    }

    public function test_reporter_id_cannot_be_manipulated(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $otherUser = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'spam',
            'description' => 'Test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Reporter should be the authenticated user, not manipulated
        $report = MarketplaceReport::where('item_id', $item->id)->first();
        $this->assertEquals($reporter->id, $report->reporter_id);
        $this->assertNotEquals($otherUser->id, $report->reporter_id);
    }

    public function test_status_cannot_be_manipulated_by_client(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($reporter);
        // Try to send status in request (should be ignored)
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'spam',
            'description' => 'Test',
            'status' => 'reviewed', // Attempt to manipulate
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report = MarketplaceReport::where('item_id', $item->id)->first();
        $this->assertEquals('pending', $report->status); // Always pending on creation
    }

    public function test_report_history_preserved_after_multiple_reports(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // Create history: pending -> dismissed -> pending -> reviewed
        $first = MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'First',
            'status' => 'dismissed',
        ]);

        $second = MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'penipuan',
            'description' => 'Second',
            'status' => 'pending',
        ]);

        // Update second to reviewed (simulate moderator action)
        $second->update(['status' => 'reviewed']);

        $third = MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'tidak_sesuai',
            'description' => 'Third',
            'status' => 'pending',
        ]);

        // All three should exist
        $this->assertDatabaseCount('marketplace_reports', 3);

        $reports = MarketplaceReport::where('item_id', $item->id)
            ->where('reporter_id', $reporter->id)
            ->orderBy('created_at')
            ->get();

        $this->assertEquals('dismissed', $reports[0]->status);
        $this->assertEquals('reviewed', $reports[1]->status);
        $this->assertEquals('pending', $reports[2]->status);
    }

    public function test_duplicate_conflict_returns_user_friendly_error(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // Create existing pending report
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'Existing',
            'status' => 'pending',
        ]);

        $this->actingAs($reporter);
        $response = $this->post(route('tenant.marketplace.report', $item), [
            'reason' => 'penipuan',
            'description' => 'Duplicate attempt',
        ]);

        // Should return user-friendly error, not 500
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda sudah memiliki laporan pending untuk item ini.');
    }

    public function test_database_constraint_enforces_single_pending_report(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // First report
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'description' => 'First',
            'status' => 'pending',
        ]);

        // Direct insert bypassing application check should fail at database level
        $this->expectException(\Illuminate\Database\QueryException::class);
        MarketplaceReport::create([
            'item_id' => $item->id,
            'reporter_id' => $reporter->id,
            'reason' => 'penipuan',
            'description' => 'Second',
            'status' => 'pending',
        ]);
    }

    public function test_cross_kos_reporting_allowed_global_marketplace(): void
    {
        // Marketplace is GLOBAL - kos_id is provenance, not authorization boundary
        $seller1 = User::factory()->create(['role' => 'tenant']);
        $seller2 = User::factory()->create(['role' => 'tenant']);
        $reporter = User::factory()->create(['role' => 'tenant']);

        $item1 = MarketplaceItem::create([
            'user_id' => $seller1->id,
            'title' => 'Item from Seller 1',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $item2 = MarketplaceItem::create([
            'user_id' => $seller2->id,
            'title' => 'Item from Seller 2',
            'description' => 'Description',
            'price' => 200000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        // Reporter can report items from different sellers (cross-kos in global marketplace)
        $this->actingAs($reporter);

        $response1 = $this->post(route('tenant.marketplace.report', $item1), [
            'reason' => 'spam',
            'description' => 'Report item from seller 1',
        ]);
        $response1->assertSessionHas('success');

        $response2 = $this->post(route('tenant.marketplace.report', $item2), [
            'reason' => 'penipuan',
            'description' => 'Report item from seller 2',
        ]);
        $response2->assertSessionHas('success');

        $this->assertDatabaseCount('marketplace_reports', 2);
    }
}