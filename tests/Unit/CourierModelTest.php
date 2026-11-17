<?php

namespace Tests\Unit;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CourierModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_sets_default_registered_at_and_status_on_creation(): void
    {
        $courier = Courier::factory()->create([
            'registered_at' => null,
            'status' => null,
        ]);

        $this->assertInstanceOf(Carbon::class, $courier->registered_at);
        $this->assertSame('active', $courier->status);
        $this->assertIsInt($courier->level);
    }

    public function test_scope_search_matches_multi_word_terms_across_name(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        Courier::factory()->create(['name' => 'Agung Wijaya']);

        $results = Courier::query()->search('budi agung')->pluck('name')->all();

        $this->assertSame(['Budiono Hadi Agung'], $results);
    }

    public function test_scope_levels_filters_valid_levels_only(): void
    {
        Courier::factory()->create(['name' => 'Level 1', 'level' => 1]);
        Courier::factory()->create(['name' => 'Level 2', 'level' => 2]);
        Courier::factory()->create(['name' => 'Level 3', 'level' => 3]);
        Courier::factory()->create(['name' => 'Level 5', 'level' => 5]);

        $results = Courier::query()
            ->levels('2,3,99')
            ->orderBy('level')
            ->pluck('level')
            ->all();

        $this->assertSame([2, 3], $results);
    }

    public function test_scope_sorted_by_defaults_to_name_asc_and_supports_registered_at(): void
    {
        Courier::factory()->create([
            'name' => 'Zebra Courier',
            'registered_at' => now()->subDays(1),
        ]);
        Courier::factory()->create([
            'name' => 'Alpha Courier',
            'registered_at' => now()->subDays(10),
        ]);

        $byName = Courier::query()->sortedBy()->pluck('name')->all();
        $this->assertSame(['Alpha Courier', 'Zebra Courier'], $byName);

        $byRegisteredDesc = Courier::query()->sortedBy('registered_at')->pluck('name')->all();
        $this->assertSame(['Zebra Courier', 'Alpha Courier'], $byRegisteredDesc);

        $byRegisteredAsc = Courier::query()->sortedBy('registered_at', 'asc')->pluck('name')->all();
        $this->assertSame(['Alpha Courier', 'Zebra Courier'], $byRegisteredAsc);
    }
}
