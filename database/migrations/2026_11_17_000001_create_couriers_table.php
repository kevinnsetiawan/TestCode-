<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('phone', 25)->unique();
            $table->unsignedTinyInteger('level')->default(1)->index();
            $table->string('vehicle_type', 30);
            $table->string('license_plate', 20)->nullable();
            $table->string('service_area', 100)->nullable()->index();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('registered_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
