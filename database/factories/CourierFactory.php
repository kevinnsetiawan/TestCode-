<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicleType = $this->faker->randomElement(Courier::VEHICLE_TYPES);

        return [
            'code' => strtoupper($this->faker->unique()->bothify('KUR-####??')),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'level' => $this->faker->numberBetween(Courier::MIN_LEVEL, Courier::MAX_LEVEL),
            'vehicle_type' => $vehicleType,
            'license_plate' => $vehicleType === 'bicycle'
                ? null
                : strtoupper($this->faker->bothify('B #### ???')),
            'service_area' => $this->faker->randomElement([
                'Jakarta Selatan',
                'Jakarta Pusat',
                'Jakarta Barat',
                'Bandung',
                'Surabaya',
                'Tangerang',
            ]),
            'status' => $this->faker->randomElement(Courier::STATUSES),
            'registered_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
