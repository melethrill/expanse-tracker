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
        $category = Category::where('name', 'Food')->first();

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 1550,
            'description' => 'Lunch',
            'date' => '2026-01-15',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('€15.50');
        $response->assertSee('Expense');
        $response->assertSee('Card');
        $response->assertSee('15/01/2026');
    }

    public function test_authenticated_user_can_create_card_expense_transaction_stored_in_cents(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Food')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'category_id' => $category->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => '45.50',
            'description' => 'Grocery shopping',
            'date' => '2026-01-15',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 4550, // Stored in cents (45.50 * 100)
            'description' => 'Grocery shopping',
            'date' => '2026-01-15',
        ]);
    }

    public function test_authenticated_user_can_create_cash_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Housing')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'category_id' => $category->id,
            'type' => 'income',
            'transaction_type' => 'cash',
            'amount' => '1200.00',
            'description' => 'Rental payment received',
            'date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'transaction_type' => 'cash',
            'amount' => 120000,
            'description' => 'Rental payment received',
            'date' => '2026-01-01',
        ]);
    }

    public function test_transaction_creation_requires_valid_type_and_transaction_type(): void
    {
        $user = User::factory()->create();
        $category = Category::where('name', 'Food')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'category_id' => $category->id,
            'type' => 'invalid_type',
            'transaction_type' => 'invalid_transaction_type',
            'amount' => '50.00',
            'date' => '2026-01-15',
        ]);

        $response->assertSessionHasErrors(['type', 'transaction_type']);
    }

    public function test_user_can_edit_all_fields_of_own_transaction(): void
    {
        $user = User::factory()->create();
        $category1 = Category::where('name', 'Transportation')->first();
        $category2 = Category::where('name', 'Entertainment')->first();

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category1->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 2000,
            'description' => 'Bus fare',
            'date' => '2026-01-10',
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'category_id' => $category2->id,
            'type' => 'income',
            'transaction_type' => 'cash',
            'amount' => '25.50',
            'description' => 'Concert ticket refund',
            'date' => '2026-01-20',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $category2->id,
            'type' => 'income',
            'transaction_type' => 'cash',
            'amount' => 2550,
            'description' => 'Concert ticket refund',
            'date' => '2026-01-20',
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
            'transaction_type' => 'card',
            'amount' => 5000,
            'description' => 'Doctor visit',
            'date' => '2026-01-12',
        ]);

        $response = $this->actingAs($user2)->put(route('transactions.update', $transaction), [
            'category_id' => $category->id,
            'type' => 'expense',
            'transaction_type' => 'card',
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
            'transaction_type' => 'card',
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
