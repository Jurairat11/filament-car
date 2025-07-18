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
        Schema::table('car_responses', function (Blueprint $table) {
            $table->string('temp_responsible')->nullable()->after('temp_responsible_id');
            $table->string('perm_responsible')->nullable()->after('perm_responsible_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_responses', function (Blueprint $table) {
            //
        });
    }
};
