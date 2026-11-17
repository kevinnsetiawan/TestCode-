<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_couriers_sorted_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zaki Pratama']);
        Courier::factory()->create(['name' => 'Andi Wijaya']);
        Courier::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->getJson('/api/couriers?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'email',
                        'phone',
                        'level',
                        'vehicle_type',
                        'license_plate',
                        'service_area',
                        'status',
                        'registered_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(['Andi Wijaya', 'Budi Santoso'], $names);
    }

    public function test_index_can_override_sort_to_registered_at(): void
    {
        Courier::factory()->create([
            'name' => 'Alpha Oldest',
            'registered_at' => now()->subDays(10),
        ]);
        Courier::factory()->create([
            'name' => 'Zebra Newest',
            'registered_at' => now()->subDay(),
        ]);
        Courier::factory()->create([
            'name' => 'Middle Registered',
            'registered_at' => now()->subDays(5),
        ]);

        $descResponse = $this->getJson('/api/couriers?sort_by=registered_at');
        $descResponse->assertOk();
        $this->assertSame(
            ['Zebra Newest', 'Middle Registered', 'Alpha Oldest'],
            collect($descResponse->json('data'))->pluck('name')->all(),
        );

        $ascResponse = $this->getJson('/api/couriers?sort_by=registered_at&sort_dir=asc');
        $ascResponse->assertOk();
        $this->assertSame(
            ['Alpha Oldest', 'Middle Registered', 'Zebra Newest'],
            collect($ascResponse->json('data'))->pluck('name')->all(),
        );
    }

    public function test_index_search_matches_partial_multi_word_courier_name(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Hartono']);
        Courier::factory()->create(['name' => 'Agung Nugroho']);

        $response = $this->getJson('/api/couriers?search=budi+agung');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_index_filters_by_comma_separated_levels(): void
    {
        Courier::factory()->create(['name' => 'Kurir Level 1', 'level' => 1]);
        Courier::factory()->create(['name' => 'Kurir Level 2', 'level' => 2]);
        Courier::factory()->create(['name' => 'Kurir Level 3', 'level' => 3]);
        Courier::factory()->create(['name' => 'Kurir Level 4', 'level' => 4]);
        Courier::factory()->create(['name' => 'Kurir Level 5', 'level' => 5]);

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $levels = collect($response->json('data'))->pluck('level')->all();
        $this->assertSame([2, 3], $levels);
    }

    public function test_show_returns_all_data_for_a_courier(): void
    {
        $courier = Courier::factory()->create([
            'code' => 'KUR-7777',
            'name' => 'Budiono Hadi Agung',
            'email' => 'budiono@example.com',
            'phone' => '081234567890',
            'level' => 4,
            'vehicle_type' => 'van',
            'license_plate' => 'B 1234 XYZ',
            'service_area' => 'Jakarta Selatan',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $courier->id)
            ->assertJsonPath('data.code', 'KUR-7777')
            ->assertJsonPath('data.name', 'Budiono Hadi Agung')
            ->assertJsonPath('data.email', 'budiono@example.com')
            ->assertJsonPath('data.phone', '081234567890')
            ->assertJsonPath('data.level', 4)
            ->assertJsonPath('data.vehicle_type', 'van')
            ->assertJsonPath('data.license_plate', 'B 1234 XYZ')
            ->assertJsonPath('data.service_area', 'Jakarta Selatan')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_show_returns_404_when_courier_does_not_exist(): void
    {
        $this->getJson('/api/couriers/99999')->assertNotFound();
    }

    public function test_store_validates_and_persists_new_courier_to_database(): void
    {
        $payload = [
            'code' => 'KUR-1001',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'phone' => '081298765432',
            'level' => 3,
            'vehicle_type' => 'motorcycle',
            'license_plate' => 'B 2020 KLM',
            'service_area' => 'Jakarta Barat',
            'status' => 'active',
            'registered_at' => '2026-11-01 08:30:00',
        ];

        $response = $this->postJson('/api/couriers', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'KUR-1001')
            ->assertJsonPath('data.name', 'Siti Aminah')
            ->assertJsonPath('data.level', 3);

        $this->assertDatabaseHas('couriers', [
            'code' => 'KUR-1001',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'phone' => '081298765432',
            'level' => 3,
            'vehicle_type' => 'motorcycle',
            'license_plate' => 'B 2020 KLM',
            'service_area' => 'Jakarta Barat',
            'status' => 'active',
        ]);
    }

    public function test_store_rejects_invalid_and_duplicate_inputs(): void
    {
        $existing = Courier::factory()->create([
            'code' => 'KUR-9999',
            'email' => 'existing@example.com',
            'phone' => '081111111111',
        ]);

        // Missing required fields
        $this->postJson('/api/couriers', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'email', 'phone', 'level', 'vehicle_type']);

        // Out-of-range level, invalid email/phone/vehicle_type/status, and duplicate unique fields
        $this->postJson('/api/couriers', [
            'code' => $existing->code,
            'name' => 'A',
            'email' => 'not-an-email',
            'phone' => 'abc',
            'level' => 6,
            'vehicle_type' => 'helicopter',
            'status' => 'unknown',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'email', 'phone', 'level', 'vehicle_type', 'status']);

        $this->postJson('/api/couriers', [
            'code' => 'KUR-8888',
            'name' => 'Valid Name',
            'email' => $existing->email,
            'phone' => $existing->phone,
            'level' => 0,
            'vehicle_type' => 'car',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'phone', 'level']);
    }

    public function test_update_validates_and_saves_changes_to_database(): void
    {
        $courier = Courier::factory()->create([
            'code' => 'KUR-2001',
            'name' => 'Nama Lama',
            'email' => 'lama@example.com',
            'phone' => '081200000001',
            'level' => 1,
            'vehicle_type' => 'motorcycle',
            'status' => 'active',
        ]);

        $other = Courier::factory()->create([
            'code' => 'KUR-2002',
            'email' => 'other@example.com',
            'phone' => '081200000002',
        ]);

        // Duplicate unique fields from another courier must fail
        $this->putJson("/api/couriers/{$courier->id}", [
            'code' => $other->code,
            'email' => $other->email,
            'phone' => $other->phone,
            'level' => 7,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'email', 'phone', 'level']);

        // Updating with own unique fields + changed values must succeed
        $response = $this->putJson("/api/couriers/{$courier->id}", [
            'code' => 'KUR-2001',
            'name' => 'Nama Baru',
            'email' => 'lama@example.com',
            'phone' => '081200000001',
            'level' => 5,
            'vehicle_type' => 'van',
            'service_area' => 'Surabaya',
            'status' => 'on_leave',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.level', 5)
            ->assertJsonPath('data.vehicle_type', 'van')
            ->assertJsonPath('data.status', 'on_leave');

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'Nama Baru',
            'level' => 5,
            'vehicle_type' => 'van',
            'service_area' => 'Surabaya',
            'status' => 'on_leave',
        ]);
    }

    public function test_destroy_deletes_courier_from_database(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->deleteJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Courier deleted successfully.']);

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);
    }

    public function test_destroy_returns_404_when_courier_does_not_exist(): void
    {
        $this->deleteJson('/api/couriers/99999')->assertNotFound();
    }
}
