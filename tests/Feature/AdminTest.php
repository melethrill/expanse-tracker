<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.users'));
        $response->assertStatus(403);

        $responseCategories = $this->actingAs($user)->get(route('admin.categories.index'));
        $responseCategories->assertStatus(403);
    }

    public function test_admin_user_can_view_all_registered_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.users'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_admin_user_can_create_new_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Education',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Education',
        ]);
    }

    public function test_admin_user_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::where('name', 'Food')->first();

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Groceries & Dining',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Groceries & Dining',
        ]);
    }

    public function test_admin_user_can_delete_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::where('name', 'Healthcare')->first();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
