<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_authenticated_user_can_view_transactions_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_expense_transaction_stored_in_cents(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Food')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '45.50',
            'description' => 'Grocery shopping',
            'date' => '2026-01-15',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 4550, // Stored in cents (45.50 * 100)
            'description' => 'Grocery shopping',
            'date' => '2026-01-15',
        ]);
    }

    public function test_authenticated_user_can_create_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Housing')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => '1200.00',
            'description' => 'Rental income',
            'date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 120000,
            'description' => 'Rental income',
        ]);
    }

    public function test_user_can_edit_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Transportation')->first();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 2000,
            'description' => 'Bus fare',
            'date' => '2026-01-10',
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '25.00',
            'description' => 'Taxi fare',
            'date' => '2026-01-10',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 2500,
            'description' => 'Taxi fare',
        ]);
    }

    public function test_user_cannot_edit_another_users_transaction(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::where('name', 'Healthcare')->first();

        $transaction = Transaction::create([
            'user_id' => $user1->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 5000,
            'description' => 'Doctor visit',
            'date' => '2026-01-12',
        ]);

        $response = $this->actingAs($user2)->put(route('transactions.update', $transaction), [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => '100.00',
            'description' => 'Modified',
            'date' => '2026-01-12',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Entertainment')->first();
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 1500,
            'description' => 'Movie ticket',
            'date' => '2026-01-14',
        ]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }
}
