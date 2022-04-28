<?php
namespace Database\Factories;

use Tests\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feature::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'invoice',
            'dtype' => 'numeric',
            'value' => 100,
            'usage' => 0
        ];
    }

    public function fullyUsed()
    {
        return $this->state(function (array $attributes) {
            return [
                'usage' => $attributes['value'],
            ];
        });
    }
}