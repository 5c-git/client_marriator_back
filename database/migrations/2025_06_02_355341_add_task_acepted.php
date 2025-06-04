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
        Schema::create('accept_task', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->index()->nullable(false);
            $table->unsignedBigInteger('user_id')->index()->nullable(false);
            $table->boolean('accepted')->default(false);
            $table->primary(['task_id', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accept_task');
    }
};
