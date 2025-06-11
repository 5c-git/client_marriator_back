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
            $table->time('change_order')->nullable(true);
            $table->time('cancel_order')->nullable(true);
            $table->time('live_order')->nullable(true);

            $table->time('change_task')->nullable(true);
            $table->time('cancel_task')->nullable(true);
            $table->time('live_task')->nullable(true);

            $table->time('repeat_bid')->nullable(true);
            $table->time('leave_bid')->nullable(true);

            $table->time('refusal_task')->nullable(true);
            $table->integer('waiting_task')->nullable(true);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'change_order','cancel_order','live_order','change_task',
                'cancel_task','live_task','repeat_bid','leave_bid',
                'refusal_task','waiting_task'
            ]);
        });
    }
};
