<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchRequest;
use App\Models\CallCard;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DistributionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_json_is_scoped_to_the_authenticated_account(): void
    {
        $account = User::factory()->create();
        $otherAccount = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $type = EsimType::create(['name' => 'Test eSIM', 'product_id' => 'test-product', 'status' => 'active']);

        $accountBatchId = (string) Str::uuid();
        $otherBatchId = (string) Str::uuid();

        Batch::create([
            'batch_id' => $accountBatchId,
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'status' => Batch::STATUS_GENERATED,
            'total_cards' => 1,
            'generated_by' => $admin->id,
        ]);

        Batch::create([
            'batch_id' => $otherBatchId,
            'account_id' => $otherAccount->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'status' => Batch::STATUS_GENERATED,
            'total_cards' => 1,
            'generated_by' => $admin->id,
        ]);

        CallCard::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Account card',
            'prefix' => '223',
            'total_minutes' => 100,
            'used_minutes' => 0,
            'created_by' => $admin->id,
            'account_id' => $account->id,
            'batch_id' => $accountBatchId,
        ]);

        CallCard::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Other card',
            'prefix' => '223',
            'total_minutes' => 100,
            'used_minutes' => 20,
            'created_by' => $admin->id,
            'account_id' => $otherAccount->id,
            'batch_id' => $otherBatchId,
        ]);

        EsimCode::create([
            'uuid' => (string) Str::uuid(),
            'esim_type_id' => $type->id,
            'product_id' => $type->product_id,
            'account_id' => $account->id,
            'batch_id' => $accountBatchId,
            'status' => 'used',
            'used_at' => now(),
        ]);

        EsimCode::create([
            'uuid' => (string) Str::uuid(),
            'esim_type_id' => $type->id,
            'product_id' => $type->product_id,
            'account_id' => $otherAccount->id,
            'batch_id' => $otherBatchId,
            'status' => 'unused',
        ]);

        $this->actingAs($account)
            ->getJson('/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.total', 2)
            ->assertJsonPath('metrics.activated', 1)
            ->assertJsonPath('metrics.remaining', 1)
            ->assertJsonPath('metrics.activation_rate', 50)
            ->assertJsonPath('batches.0.batch_id', $accountBatchId);
    }

    public function test_account_can_submit_pending_batch_request(): void
    {
        $account = User::factory()->create();

        $this->actingAs($account)
            ->post('/request-cards', [
                'product_type' => BatchRequest::PRODUCT_CALL_CARD,
                'quantity' => 25,
                'notes' => 'Next weekly allocation',
            ])
            ->assertRedirect(route('account.requests.create'));

        $this->assertDatabaseHas('batch_requests', [
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'quantity' => 25,
            'status' => BatchRequest::STATUS_PENDING,
        ]);

        $batchRequest = BatchRequest::where('account_id', $account->id)->firstOrFail();

        $this->actingAs($account)
            ->get('/request-cards')
            ->assertOk()
            ->assertSee('Submitted Requests')
            ->assertSee('Pending and processed card requests')
            ->assertSee('Submit a request for new eSIM or call card stock. Admin will review and generate a batch after approval.')
            ->assertSee('Create Request')
            ->assertSee('Request ID')
            ->assertSee('Product')
            ->assertSee('Product Type')
            ->assertSee('Quantity')
            ->assertSee('Status')
            ->assertSee('Submitted Date')
            ->assertSee('Notes')
            ->assertSee('Batch')
            ->assertSee('#'.$batchRequest->id)
            ->assertSee('Call card')
            ->assertSee('Pending')
            ->assertSee('Next weekly allocation')
            ->assertSee('Not generated yet')
            ->assertSee('Batch will be created after admin approval and generation')
            ->assertSee('Submit Request')
            ->assertSee('id="createRequestModal"', false)
            ->assertSee("params.get('open') === 'create'", false)
            ->assertSee('window.history.replaceState', false)
            ->assertSee('account-modal');
    }

    public function test_request_cards_page_lists_only_the_authenticated_accounts_requests(): void
    {
        $account = User::factory()->create();
        $otherAccount = User::factory()->create();

        BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'quantity' => 40,
            'status' => BatchRequest::STATUS_PENDING,
            'notes' => 'Visible allocation',
        ]);

        BatchRequest::create([
            'account_id' => $otherAccount->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'quantity' => 80,
            'status' => BatchRequest::STATUS_PENDING,
            'notes' => 'Hidden allocation',
        ]);

        $this->actingAs($account)
            ->get('/request-cards')
            ->assertOk()
            ->assertSee('Submitted Requests')
            ->assertSee('Visible allocation')
            ->assertDontSee('Hidden allocation');
    }

    public function test_request_cards_page_shows_batch_identifier_after_generation(): void
    {
        $account = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $batchId = (string) Str::uuid();

        $batchRequest = BatchRequest::create([
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'quantity' => 50,
            'status' => BatchRequest::STATUS_GENERATED,
            'notes' => 'Generated request',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'generated_at' => now(),
        ]);

        Batch::create([
            'batch_id' => $batchId,
            'account_id' => $account->id,
            'batch_request_id' => $batchRequest->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'status' => Batch::STATUS_GENERATED,
            'total_cards' => 50,
            'generated_by' => $admin->id,
        ]);

        $this->actingAs($account)
            ->get('/request-cards')
            ->assertOk()
            ->assertSee('Generated request')
            ->assertSee('Generated')
            ->assertSee('Batch #'.$batchId)
            ->assertDontSee('Not generated yet');
    }

    public function test_request_cards_page_empty_state_has_create_action(): void
    {
        $account = User::factory()->create();

        $this->actingAs($account)
            ->get('/request-cards')
            ->assertOk()
            ->assertSee('Submitted Requests')
            ->assertSee('No requests submitted yet.')
            ->assertSee('Create Request');
    }

    public function test_my_batches_pages_render_assigned_batch_listing_only(): void
    {
        $account = User::factory()->create();
        $otherAccount = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $accountBatchId = (string) Str::uuid();
        $otherBatchId = (string) Str::uuid();

        Batch::create([
            'batch_id' => $accountBatchId,
            'account_id' => $account->id,
            'product_type' => BatchRequest::PRODUCT_CALL_CARD,
            'status' => Batch::STATUS_GENERATED,
            'total_cards' => 2,
            'generated_by' => $admin->id,
            'delivery_document_path' => 'delivery-documents/test.pdf',
        ]);

        Batch::create([
            'batch_id' => $otherBatchId,
            'account_id' => $otherAccount->id,
            'product_type' => BatchRequest::PRODUCT_ESIM,
            'status' => Batch::STATUS_SENT,
            'total_cards' => 1,
            'generated_by' => $admin->id,
        ]);

        CallCard::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Used card',
            'prefix' => '223',
            'total_minutes' => 100,
            'used_minutes' => 25,
            'created_by' => $admin->id,
            'account_id' => $account->id,
            'batch_id' => $accountBatchId,
        ]);

        CallCard::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Unused card',
            'prefix' => '223',
            'total_minutes' => 100,
            'used_minutes' => 0,
            'created_by' => $admin->id,
            'account_id' => $account->id,
            'batch_id' => $accountBatchId,
        ]);

        $this->actingAs($account)
            ->get('/my-batches')
            ->assertOk()
            ->assertSee('Batch Overview')
            ->assertSee('Total Cards')
            ->assertSee('Used Cards')
            ->assertSee('Remaining Cards')
            ->assertSee('Activation Rate')
            ->assertSee('Created Date')
            ->assertSee($accountBatchId)
            ->assertSee('Call card')
            ->assertSee('generated')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('50%')
            ->assertSee('Document')
            ->assertSee('Need more stock? Submit a new request first.')
            ->assertSee('Submit Batch Request')
            ->assertSee('/request-cards?open=create')
            ->assertDontSee('Request New Batch')
            ->assertDontSee($otherBatchId)
            ->assertDontSee('Product Type')
            ->assertDontSee('Submit Request');

        $this->actingAs($account)
            ->get('/batches')
            ->assertOk()
            ->assertSee($accountBatchId)
            ->assertDontSee($otherBatchId);
    }

    public function test_my_batches_page_keeps_empty_state_and_secondary_request_action(): void
    {
        $account = User::factory()->create();

        $this->actingAs($account)
            ->get('/my-batches')
            ->assertOk()
            ->assertSee('Batch Overview')
            ->assertSee('No batches assigned yet.')
            ->assertSee('Need more stock? Submit a new request first.')
            ->assertSee('Submit Batch Request')
            ->assertSee('/request-cards?open=create')
            ->assertDontSee('Request New Batch')
            ->assertDontSee('Product Type')
            ->assertDontSee('Submit Request');
    }

    public function test_dashboard_page_renders_reference_sections_and_empty_states(): void
    {
        $account = User::factory()->create(['name' => 'AFRITEL Distributor']);

        $this->actingAs($account)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('DASHBOARD')
            ->assertSee('Welcome back, AFRITEL Distributor')
            ->assertSee('Total Cards Received')
            ->assertSee('Cards Activated')
            ->assertSee('Cards Remaining')
            ->assertSee('Activation Rate')
            ->assertSee('Batch Overview')
            ->assertSee('Recent Activity')
            ->assertSee('Request Status')
            ->assertSee('Quick Actions')
            ->assertSee('Request New Batch')
            ->assertSee('View All Batches')
            ->assertSee('No batches assigned yet.')
            ->assertSee('No recent activity yet.')
            ->assertSee('No requests yet.');
    }

    public function test_reports_page_renders_report_analytics_instead_of_dashboard_actions(): void
    {
        $account = User::factory()->create();

        $this->actingAs($account)
            ->get('/reports')
            ->assertOk()
            ->assertSee('REPORTS')
            ->assertSee('Distribution performance overview')
            ->assertSee('Total Cards Received')
            ->assertSee('Total Activated')
            ->assertSee('Total Remaining')
            ->assertSee('Expired Cards')
            ->assertSee('Requests by Status')
            ->assertSee('Requests by Product')
            ->assertSee('Batch Performance')
            ->assertSee('Inventory Status')
            ->assertSee('No batch performance data yet.')
            ->assertDontSee('Quick Actions');
    }
}
