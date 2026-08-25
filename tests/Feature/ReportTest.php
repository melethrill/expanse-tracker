<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_report_calculates_total_expenses_per_category_correctly(): void
    {
        $user = User::factory()->create();
        $food = Category::where('name', 'Food')->first();
        $housing = Category::where('name', 'Housing')->first();

        // Create food expenses (45.00 + 15.50 = 60.50 -> 6050 cents)
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 4500,
            'date' => '2026-01-10',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'cash',
            'amount' => 1550,
            'date' => '2026-01-11',
        ]);

        // Income for food should not count towards expenses
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'income',
            'transaction_type' => 'card',
            'amount' => 10000,
            'date' => '2026-01-12',
        ]);

        // Create housing expense (1200.00 -> 120000 cents)
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $housing->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 120000,
            'date' => '2026-01-01',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('€1,260.50');
        $response->assertSee('€60.50');
        $response->assertSee('€1,200.00');
        $response->assertViewHas('totalExpenseCents', 126050);
        $response->assertViewHas('categoryExpenses', function ($categoryExpenses) use ($food, $housing) {
            $foodExpense = $categoryExpenses->firstWhere('id', $food->id);
            $housingExpense = $categoryExpenses->firstWhere('id', $housing->id);

            return $foodExpense->total_cents == 6050 && $housingExpense->total_cents == 120000;
        });
    }

    public function test_report_filters_expenses_by_year_and_month(): void
    {
        $user = User::factory()->create();
        $food = Category::where('name', 'Food')->first();

        // Transaction in Jan 2026
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 5000,
            'date' => '2026-01-15',
        ]);

        // Transaction in Feb 2026
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 3000,
            'date' => '2026-02-10',
        ]);

        // Transaction in Jan 2025
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 2000,
            'date' => '2025-01-20',
        ]);

        // Filter for Jan 2026
        $response = $this->actingAs($user)->get(route('reports.index', ['year' => '2026', 'month' => '1']));
        $response->assertStatus(200);
        $response->assertViewHas('totalExpenseCents', 5000);

        // Filter for year 2026 only
        $responseYear = $this->actingAs($user)->get(route('reports.index', ['year' => '2026']));
        $responseYear->assertStatus(200);
        $responseYear->assertViewHas('totalExpenseCents', 8000);

        // Filter for month 1 only (Jan 2026 + Jan 2025)
        $responseMonth = $this->actingAs($user)->get(route('reports.index', ['month' => '1']));
        $responseMonth->assertStatus(200);
        $responseMonth->assertViewHas('totalExpenseCents', 7000);
    }

    public function test_report_only_includes_authenticated_users_expenses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $food = Category::where('name', 'Food')->first();

        Transaction::create([
            'user_id' => $user1->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 3000,
            'date' => '2026-01-10',
        ]);

        Transaction::create([
            'user_id' => $user2->id,
            'category_id' => $food->id,
            'type' => 'expense',
            'transaction_type' => 'card',
            'amount' => 5000,
            'date' => '2026-01-10',
        ]);

        $response = $this->actingAs($user1)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalExpenseCents', 3000);
    }
}
