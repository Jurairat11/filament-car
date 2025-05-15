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
        Schema::create('car_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('car_reports','id')->onDelete('cascade');
            $table->text('cause');
            $table->string('img_after');
            $table->text('temp_desc')->nullable();
            $table->date('temp_due_date')->nullable();
            $table->foreignId('temp_responsible_id')->nullable()->constrained('users','id')->onDelete('set null');
            $table->text('perm_desc')->nullable();
            $table->date('perm_due_date')->nullable();
            $table->foreignId('perm_responsible_id')->nullable()->constrained('users','id')->onDelete('set null');
            $table->text('preventive');
            $table->string('status');
            $table->foreignId('created_by')->constrained('users','id')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_responses');
    }
};
