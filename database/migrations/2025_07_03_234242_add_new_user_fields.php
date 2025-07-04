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

        Schema::table('users', function (Blueprint $table) {
            $table->integer('count_wait_bid')->nullable(true);
            $table->integer('time_answer_bid')->nullable(true);
            $table->integer('notification_start')->nullable(true);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'count_wait_bid','time_answer_bid','notification_start'
            ]);
        });
    }
};
