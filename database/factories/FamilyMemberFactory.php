<?php

namespace Database\Factories;

use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyMember>
 */
class FamilyMemberFactory extends Factory
{
    protected $model = FamilyMember::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'dob' => fake()->date('Y-m-d', '-20 years'),
            'family_register_number' => fake()->numerify('FR-#####'),
            'amka' => fake()->numerify('###########'),
            'vat_number' => fake()->numerify('#########'),
            'pa_number' => fake()->numerify('PA-#####'),
            'id_number' => fake()->bothify('??######'),
            'passport_number' => fake()->bothify('P#######'),
        ];
    }
}
