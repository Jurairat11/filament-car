<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('car_reports', function (Blueprint $table) {
            $table->dropForeign(['problem_id']);
            $table->unsignedBigInteger('problem_id')->nullable()->change();
            $table->foreign('problem_id')
                ->references('id')->on('problems')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('car_reports', function (Blueprint $table) {
            $table->dropForeign(['problem_id']);
            $table->unsignedBigInteger('problem_id')->nullable(false)->change();
            $table->foreign('problem_id')
                ->references('id')->on('problems')
                ->onDelete('cascade');
        });
    }
};
