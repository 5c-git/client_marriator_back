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
        Schema::create('supervisor_specialist', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_supervisor')->index()->nullable(false);
            $table->unsignedBigInteger('user_id_specialist')->index()->nullable(false);
            $table->primary(['user_id_supervisor', 'user_id_specialist']);
        });

        Schema::create('manager_specialist', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_manager')->index()->nullable(false);
            $table->unsignedBigInteger('user_id_specialist')->index()->nullable(false);
            $table->primary(['user_id_manager', 'user_id_specialist']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_specialist');
        Schema::dropIfExists('manager_specialist');
    }
};
