<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 123123123
        ]);
        $firstUser->preference()->create([
            'user_id' => $firstUser->id,
            'preferred_sources' => json_encode(['NewsAPI', 'The New York Times','Guardian']),
            'preferred_categories' => json_encode(['science', 'technology','health', 'business', 'Travel','Entertainment']),
            'preferred_authors' => json_encode(['author1', 'author2'])
        ]);
        User::factory(10)
            ->create()
            ->each(function ($user) {
                UserPreference::create([
                    'user_id' => $user->id,
                    'preferred_sources' => json_encode(
                        Arr::random(['NewsAPI', 'The New York Times', 'Guardian'], rand(1, 3))
                    ),
                    'preferred_categories' => json_encode(
                        Arr::random(['science', 'technology', 'health', 'business', 'travel', 'entertainment'], rand(1, 4))
                    ),
                    'preferred_authors' => json_encode(
                        Arr::random(['author1', 'author2', 'author3', 'author4'], rand(1, 2))
                    ),
                ]);
            });
    }
}
