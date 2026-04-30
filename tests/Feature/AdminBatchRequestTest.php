<?php

namespace Tests\Feature;

use App\Models\BatchRequest;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBatchRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_batch_requests_lists_active_esim_plans_for_esim_requests(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $account = User::factory()->create();

        $activePlan = EsimType::create([
            'name' => 'Global 5GB',
            'product_id' => 'global-5gb',
            'status' => 'active',
        ]);

        EsimType::create([
            'name' => 'Inactive Plan',
            'product_id' => 'inactive-plan',
            'status' => 'inactive',
        ]);

        BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'quantity' => 5,
            'status' => BatchRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.batch-requests.index'))
            ->assertOk()
            ->assertSee('eSIM Plan')
            ->assertSee($activePlan->name)
            ->assertSee($activePlan->product_id)
            ->assertSee('Only active eSIM plans with a product ID are listed.')
            ->assertDontSee('Inactive Plan')
            ->assertDontSee('No eSIM plans available. Please create a plan first.');
    }

    public function test_admin_batch_requests_shows_empty_state_when_no_esim_plans_are_available(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $account = User::factory()->create();

        BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'quantity' => 5,
            'status' => BatchRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.batch-requests.index'))
            ->assertOk()
            ->assertSee('No eSIM plans available. Please create a plan first.')
            ->assertSee(route('admin.esim-types.create'))
            ->assertDontSee('Select plan');
    }

    public function test_call_card_requests_do_not_show_the_esim_plan_dropdown(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $account = User::factory()->create();

        EsimType::create([
            'name' => 'Global 5GB',
            'product_id' => 'global-5gb',
            'status' => 'active',
        ]);

        BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'quantity' => 5,
            'status' => BatchRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.batch-requests.index'))
            ->assertOk()
            ->assertSee('Dial Prefix')
            ->assertSee('Call Card Minutes')
            ->assertDontSee('eSIM Plan')
            ->assertDontSee('Select plan');
    }

    public function test_esim_generation_rejects_inactive_or_incomplete_plans(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $account = User::factory()->create();

        $inactivePlan = EsimType::create([
            'name' => 'Inactive Plan',
            'product_id' => 'inactive-plan',
            'status' => 'inactive',
        ]);

        $batchRequest = BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'quantity' => 5,
            'status' => BatchRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.batch-requests.index'))
            ->post(route('admin.batch-requests.generate', $batchRequest), [
                'esim_type_id' => $inactivePlan->id,
                'label' => 'Request #'.$batchRequest->id,
            ])
            ->assertRedirect(route('admin.batch-requests.index'))
            ->assertSessionHasErrors('esim_type_id');

        $this->assertDatabaseMissing('batches', [
            'batch_request_id' => $batchRequest->id,
        ]);
        $this->assertSame(0, EsimCode::count());
    }

    public function test_esim_generation_uses_selected_active_plan(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $account = User::factory()->create();

        $activePlan = EsimType::create([
            'name' => 'Global 5GB',
            'product_id' => 'global-5gb',
            'status' => 'active',
        ]);

        $batchRequest = BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'quantity' => 2,
            'status' => BatchRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.batch-requests.generate', $batchRequest), [
                'esim_type_id' => $activePlan->id,
                'label' => 'Distributor stock',
            ])
            ->assertRedirect(route('admin.batch-requests.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('batch_requests', [
            'id' => $batchRequest->id,
            'status' => BatchRequest::STATUS_GENERATED,
        ]);

        $this->assertDatabaseHas('esim_codes', [
            'esim_type_id' => $activePlan->id,
            'product_id' => $activePlan->product_id,
            'account_id' => $account->id,
            'label' => 'Distributor stock',
            'status' => 'unused',
        ]);
        $this->assertSame(2, EsimCode::count());
    }
}
