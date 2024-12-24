<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'preferred_sources' => json_encode(['source1', 'source2']),
            'preferred_categories' => json_encode(['category1', 'category2']),
            'preferred_authors' => json_encode(['author1', 'author2']),
        ];
    }
}
