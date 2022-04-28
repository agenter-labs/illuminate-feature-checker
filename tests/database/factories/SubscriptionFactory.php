<?php
namespace Database\Factories;

use Tests\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;


    public static $id = 0;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        self::$id += 1;

        return [
            'end_date' => time() + (60*60*24),
            'is_deleted' => $this->faker->boolean() ? 1 : 0,
            'id' => self::$id
        ];
    }
}