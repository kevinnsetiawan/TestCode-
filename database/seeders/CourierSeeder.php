<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Seed the couriers table with representative sample data.
     */
    public function run(): void
    {
        $samples = [
            [
                'code' => 'KUR-0001',
                'name' => 'Budiono Hadi Agung',
                'email' => 'budiono.agung@example.com',
                'phone' => '081234567890',
                'level' => 3,
                'vehicle_type' => 'motorcycle',
                'license_plate' => 'B 4123 XYZ',
                'service_area' => 'Jakarta Selatan',
                'status' => 'active',
                'registered_at' => now()->subDays(30),
            ],
            [
                'code' => 'KUR-0002',
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@example.com',
                'phone' => '081234567891',
                'level' => 2,
                'vehicle_type' => 'van',
                'license_plate' => 'B 9876 ABC',
                'service_area' => 'Jakarta Pusat',
                'status' => 'active',
                'registered_at' => now()->subDays(10),
            ],
            [
                'code' => 'KUR-0003',
                'name' => 'Citra Dewi Lestari',
                'email' => 'citra.lestari@example.com',
                'phone' => '081234567892',
                'level' => 5,
                'vehicle_type' => 'truck',
                'license_plate' => 'D 5544 KLM',
                'service_area' => 'Bandung',
                'status' => 'active',
                'registered_at' => now()->subDays(5),
            ],
        ];

        foreach ($samples as $sample) {
            Courier::updateOrCreate(
                ['code' => $sample['code']],
                $sample,
            );
        }

        if (Courier::count() < 20) {
            Courier::factory()->count(20 - Courier::count())->create();
        }
    }
}
