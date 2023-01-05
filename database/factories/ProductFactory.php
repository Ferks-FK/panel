<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(60),
            'price' => $this->faker->numberBetween(0, 1000),
            'memory' => $this->faker->numberBetween(32, 1024),
            'disk' => $this->faker->numberBetween(500, 5000),
            'databases' => $this->faker->numberBetween(1, 10),
        ];
    }
}
