<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_settings')) {
            Schema::create('school_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->unsignedInteger('radius_meters')->default(100);
                $table->timestamps();
            });
        }

        if (DB::table('school_settings')->doesntExist()) {
            DB::table('school_settings')->insert([
                'latitude' => (float) env('SCHOOL_LATITUDE', -7.671325),
                'longitude' => (float) env('SCHOOL_LONGITUDE', 110.589607),
                'radius_meters' => (int) env('SCHOOL_RADIUS_METERS', 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
