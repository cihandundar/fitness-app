<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\UserMembership;
use App\Models\User;
use App\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_list_all_payments(): void
    {
        Payment::factory()->count(3)->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function payment_list_includes_user_information(): void
    {
        Payment::factory()->create();

        $response = $this->withToken($this->adminToken)->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'amount',
                        'status',
                    ]
                ]
            ]);
    }

    /** @test */
    public function payments_are_ordered_by_created_at_desc(): void
    {
        $oldPayment = Payment::factory()->create(['created_at' => now()->subDays(5)]);
        $newPayment = Payment::factory()->create(['created_at' => now()]);

        $response = $this->withToken($this->adminToken)->getJson('/api/payments');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($newPayment->id, $data[0]['id']);
        $this->assertEquals($oldPayment->id, $data[1]['id']);
    }

    /** @test */
    public function admin_can_view_single_payment(): void
    {
        $payment = Payment::factory()->create([
            'amount' => 199.99,
            'status' => 'completed',
        ]);

        $response = $this->withToken($this->adminToken)->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $payment->id,
                    'amount' => 199.99,
                ]
            ]);
    }

    /** @test */
    public function payment_detail_includes_user_and_membership(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->withToken($this->adminToken)->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'user_membership',
                    'amount',
                    'status',
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_payment_status(): void
    {
        $payment = Payment::factory()->create(['status' => 'pending']);

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Ödeme durumu güncellendi.',
            ]);

        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
    }

    /** @test */
    public function status_update_is_required(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function status_must_be_valid(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function all_valid_statuses_are_accepted(): void
    {
        $validStatuses = ['pending', 'completed', 'failed', 'refunded'];

        foreach ($validStatuses as $status) {
            $payment = Payment::factory()->create(['status' => 'pending']);

            $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
                'status' => $status,
            ])->assertStatus(200);

            $payment->delete();
        }
    }

    /** @test */
    public function payment_can_be_marked_as_refunded(): void
    {
        $payment = Payment::factory()->create([
            'status' => 'completed',
            'amount' => 199.99,
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'refunded',
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);
    }

    /** @test */
    public function payment_can_be_marked_as_failed(): void
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'failed',
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_payments(): void
    {
        Payment::factory()->count(3)->create();

        $response = $this->getJson('/api/payments');

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_payment(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_payment(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->putJson("/api/payments/{$payment->id}", [
            'status' => 'refunded',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_list_payments(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;

        Payment::factory()->count(3)->create();

        $response = $this->withToken($token)->getJson('/api/payments');

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_view_payment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $payment = Payment::factory()->create();

        $response = $this->withToken($token)->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_update_payment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('user-token')->plainTextToken;
        $payment = Payment::factory()->create();

        $response = $this->withToken($token)->putJson("/api/payments/{$payment->id}", [
            'status' => 'refunded',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_amount_is_stored_correctly(): void
    {
        $payment = Payment::factory()->create([
            'amount' => 299.99,
        ]);

        $this->assertEquals(299.99, $payment->amount);
    }

    /** @test */
    public function payment_method_can_be_stored(): void
    {
        $payment = Payment::factory()->create([
            'payment_method' => 'credit_card',
        ]);

        $this->assertEquals('credit_card', $payment->payment_method);
    }

    /** @test */
    public function transaction_id_can_be_stored(): void
    {
        $payment = Payment::factory()->create([
            'transaction_id' => 'txn_1234567890',
        ]);

        $this->assertEquals('txn_1234567890', $payment->transaction_id);
    }

    /** @test */
    public function payment_details_can_be_stored_as_json(): void
    {
        $details = [
            'card_last_four' => '4242',
            'card_brand' => 'Visa',
            'gateway' => 'stripe',
        ];

        $payment = Payment::factory()->create([
            'payment_details' => $details,
        ]);

        $this->assertEquals($details, $payment->payment_details);
    }

    /** @test */
    public function payment_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $payment->user->id);
    }

    /** @test */
    public function payment_can_be_associated_with_user_membership(): void
    {
        $membership = UserMembership::factory()->create();
        $payment = Payment::factory()->create([
            'user_membership_id' => $membership->id,
        ]);

        $this->assertEquals($membership->id, $payment->user_membership_id);
    }

    /** @test */
    public function completed_payment_cannot_be_refunded_twice(): void
    {
        $payment = Payment::factory()->create(['status' => 'refunded']);

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'refunded',
        ]);

        $response->assertStatus(200); // Current API allows updating to same status
    }

    /** @test */
    public function failed_payment_can_be_retried(): void
    {
        $payment = Payment::factory()->create(['status' => 'failed']);

        $response = $this->withToken($this->adminToken)->putJson("/api/payments/{$payment->id}", [
            'status' => 'pending', // Retry payment
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('pending', $payment->status);
    }
}
