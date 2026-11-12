<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polybags', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('block', 10)->index();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polybag_id')->constrained()->cascadeOnDelete();
            $table->decimal('soil_moisture', 5, 2);
            $table->decimal('nitrogen', 7, 2);
            $table->decimal('phosphorus', 7, 2);
            $table->decimal('potassium', 7, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['polybag_id', 'recorded_at']);
        });

        Schema::create('climate_readings', function (Blueprint $table) {
            $table->id();
            $table->decimal('temperature', 5, 2);
            $table->decimal('humidity', 5, 2);
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });

        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polybag_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);
            $table->string('label');
            $table->decimal('confidence', 5, 2);
            $table->string('severity', 10);
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('scanned_at')->index();
            $table->timestamps();
            $table->index(['polybag_id', 'type']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polybag_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('severity', 10);
            $table->text('message');
            $table->string('status', 10)->default('active')->index();
            $table->string('source', 10)->default('system');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'polybag_id', 'status']);
        });

        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->decimal('value', 8, 2);
            $table->string('unit', 20);
            $table->timestamps();
        });

        $now = now();
        DB::table('thresholds')->insert([
            ['key' => 'humidity_max', 'label' => 'Kelembapan udara maksimum', 'value' => 85, 'unit' => '%', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'soil_moisture_min', 'label' => 'Kadar air tanah minimum', 'value' => 45, 'unit' => '%', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'nitrogen_min', 'label' => 'Nitrogen minimum', 'value' => 30, 'unit' => 'ppm', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'phosphorus_min', 'label' => 'Fosfor minimum', 'value' => 20, 'unit' => 'ppm', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'potassium_min', 'label' => 'Kalium minimum', 'value' => 120, 'unit' => 'ppm', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('thresholds');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('climate_readings');
        Schema::dropIfExists('sensor_readings');
        Schema::dropIfExists('polybags');
    }
};
