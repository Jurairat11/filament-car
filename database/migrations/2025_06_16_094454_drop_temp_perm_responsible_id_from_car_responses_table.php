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
            // $table->dropForeign('temp_responsible_id');
            // $table->dropForeign('perm_responsible_id');
            //This database driver does not support dropping foreign keys by name.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_responses', function (Blueprint $table) {
            // $table->foreignId('temp_responsible_id')->nullable()->constrained('users','id')->onDelete('set null');
            // $table->foreignId('perm_responsible_id')->nullable()->constrained('users','id')->onDelete('set null');
        });
    }
};
