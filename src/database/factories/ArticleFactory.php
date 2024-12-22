<?php
namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'source' => $this->faker->url,
            'author' => $this->faker->name,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'metadata' => json_encode([
                'tags' => $this->faker->words(3),
                'categories' => $this->faker->words(2),
            ]),
        ];
    }
}
