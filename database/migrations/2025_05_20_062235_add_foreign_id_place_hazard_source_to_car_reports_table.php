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
        Schema::table('car_reports', function (Blueprint $table) {
            $table->foreignId('place_id')->nullable()->constrained('places')->onDelete('set null');
            $table->foreignId('hazard_source_id')->nullable()->constrained('hazard_sources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_reports', function (Blueprint $table) {
            //
        });
    }
};
