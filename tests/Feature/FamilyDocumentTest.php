<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FamilyDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_upload_document_for_family_member(): void
    {
        Storage::fake('public');

        $member = FamilyMember::factory()->create();
        $file = UploadedFile::fake()->create('birth_certificate.pdf', 500);

        $response = $this->actingAs($this->user)->post(route('family-documents.store', $member), [
            'title' => 'Birth Certificate',
            'file' => $file,
        ]);

        $response->assertRedirect(route('family-members.edit', $member));

        $this->assertDatabaseHas('family_documents', [
            'family_member_id' => $member->id,
            'title' => 'Birth Certificate',
        ]);

        $doc = $member->documents()->first();
        $this->assertNotNull($doc->file_path);
        Storage::disk('public')->assertExists($doc->file_path);
    }

    public function test_can_download_document(): void
    {
        Storage::fake('public');

        $member = FamilyMember::factory()->create();
        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $path = $file->store('family_documents', 'public');

        $doc = $member->documents()->create([
            'title' => 'Test Doc',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 100,
        ]);

        $response = $this->actingAs($this->user)->get(route('family-documents.download', $doc));

        $response->assertStatus(200);
    }

    public function test_can_delete_document(): void
    {
        Storage::fake('public');

        $member = FamilyMember::factory()->create();
        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $path = $file->store('family_documents', 'public');

        $doc = $member->documents()->create([
            'title' => 'Test Doc',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 100,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->user)->delete(route('family-documents.destroy', $doc));

        $response->assertRedirect(route('family-members.edit', $member));
        $this->assertDatabaseMissing('family_documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
