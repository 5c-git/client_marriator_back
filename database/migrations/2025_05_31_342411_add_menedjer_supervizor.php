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
        Schema::create('manager_supervisor', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_manager')->index()->nullable(false);
            $table->unsignedBigInteger('user_id_supervisor')->index()->nullable(false);
            $table->primary(['user_id_manager', 'user_id_supervisor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_supervisor');
    }
};
