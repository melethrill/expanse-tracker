<?php

namespace Database\Factories;

use App\Models\FamilyDocument;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyDocument>
 */
class FamilyDocumentFactory extends Factory
{
    protected $model = FamilyDocument::class;

    public function definition(): array
    {
        return [
            'family_member_id' => FamilyMember::factory(),
            'title' => fake()->words(2, true),
            'file_path' => 'family_documents/' . fake()->uuid() . '.pdf',
            'file_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1000, 500000),
        ];
    }
}
