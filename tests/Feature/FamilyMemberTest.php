<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FamilyMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_family_members(): void
    {
        $response = $this->get(route('family-members.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_family_members_index(): void
    {
        FamilyMember::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => '1990-05-15',
        ]);

        $response = $this->actingAs($this->user)->get(route('family-members.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Taurus');
    }

    public function test_can_create_family_member_with_images(): void
    {
        Storage::fake('public');

        $imageFront = UploadedFile::fake()->image('id_front.jpg');
        $imageBack = UploadedFile::fake()->image('id_back.jpg');

        $response = $this->actingAs($this->user)->post(route('family-members.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'dob' => '1995-08-25',
            'amka' => '12345678901',
            'id_number' => 'AB123456',
            'id_image_front' => $imageFront,
            'id_image_back' => $imageBack,
        ]);

        $response->assertRedirect(route('family-members.index'));
        $this->assertDatabaseHas('family_members', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'amka' => '12345678901',
        ]);

        $member = FamilyMember::first();
        $this->assertNotNull($member->id_image_front);
        $this->assertNotNull($member->id_image_back);
        Storage::disk('public')->assertExists($member->id_image_front);
        Storage::disk('public')->assertExists($member->id_image_back);
        $this->assertEquals('Virgo', $member->zodiac_sign);
    }

    public function test_zodiac_sign_calculation_for_all_signs(): void
    {
        $zodiacDates = [
            '1990-03-25' => 'Aries',
            '1990-04-25' => 'Taurus',
            '1990-05-25' => 'Gemini',
            '1990-06-25' => 'Cancer',
            '1990-07-25' => 'Leo',
            '1990-08-25' => 'Virgo',
            '1990-09-25' => 'Libra',
            '1990-10-25' => 'Scorpio',
            '1990-11-25' => 'Sagittarius',
            '1990-12-25' => 'Capricorn',
            '1990-01-05' => 'Capricorn',
            '1990-01-25' => 'Aquarius',
            '1990-02-25' => 'Pisces',
        ];

        foreach ($zodiacDates as $dob => $expectedSign) {
            $member = FamilyMember::factory()->create(['dob' => $dob]);
            $this->assertEquals($expectedSign, $member->zodiac_sign, "Failed for date: {$dob}");
        }
    }

    public function test_can_update_family_member_and_replace_image(): void
    {
        Storage::fake('public');

        $initialImage = UploadedFile::fake()->image('old.jpg');
        $member = FamilyMember::factory()->create([
            'id_image_front' => $initialImage->store('family_members/images', 'public'),
        ]);

        $oldPath = $member->id_image_front;
        Storage::disk('public')->assertExists($oldPath);

        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->user)->put(route('family-members.update', $member), [
            'first_name' => 'Updated',
            'last_name' => $member->last_name,
            'dob' => $member->dob->format('Y-m-d'),
            'id_image_front' => $newImage,
        ]);

        $response->assertRedirect(route('family-members.index'));

        $member->refresh();
        $this->assertEquals('Updated', $member->first_name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($member->id_image_front);
    }

    public function test_can_download_identity_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('passport.jpg');
        $path = $image->store('family_members/images', 'public');

        $member = FamilyMember::factory()->create([
            'passport_image' => $path,
        ]);

        $response = $this->actingAs($this->user)->get(route('family-members.download-image', [
            'familyMember' => $member,
            'type' => 'passport_image',
        ]));

        $response->assertStatus(200);
    }

    public function test_can_delete_family_member_and_associated_files(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('id.jpg');
        $path = $image->store('family_members/images', 'public');

        $member = FamilyMember::factory()->create([
            'id_image_front' => $path,
        ]);

        $docFile = UploadedFile::fake()->create('contract.pdf', 100);
        $docPath = $docFile->store('family_documents', 'public');

        $document = $member->documents()->create([
            'title' => 'Contract',
            'file_path' => $docPath,
            'file_type' => 'application/pdf',
            'file_size' => 100,
        ]);

        $response = $this->actingAs($this->user)->delete(route('family-members.destroy', $member));

        $response->assertRedirect(route('family-members.index'));
        $this->assertDatabaseMissing('family_members', ['id' => $member->id]);
        $this->assertDatabaseMissing('family_documents', ['id' => $document->id]);
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($docPath);
    }
}
