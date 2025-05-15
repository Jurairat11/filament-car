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
        Schema::create('car_reports', function (Blueprint $table) {
            $table->id();
            $table->string('car_no')->unique();
            $table->date('car_date');
            $table->date('car_due_date');
            $table->text('car_desc');
            $table->string('img_before');
            $table->string('status');

            // Foreign keys
            $table->foreignId('dept_id')->constrained('departments','dept_id')->onDelete('cascade');
            $table->foreignId('sec_id')->constrained('sections','sec_id')->onDelete('cascade');
            $table->foreignId('hazard_level_id')->constrained('hazard_levels')->onDelete('set null');
            $table->foreignId('hazard_type_id')->constrained('hazard_types')->onDelete('set null');
            $table->foreignId('problem_id')->constrained('problems','id')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users','id')->onDelete('set null');

            // Nullable foreign keys
            $table->foreignId('parent_car_id')->nullable()->constrained('car_reports')->onDelete('set null');
            $table->foreignId('followed_car_id')->nullable()->constrained('car_reports')->onDelete('set null');
            $table->foreignId('responsible_dept_id')->nullable()->constrained('departments','dept_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_reports');
    }
};
