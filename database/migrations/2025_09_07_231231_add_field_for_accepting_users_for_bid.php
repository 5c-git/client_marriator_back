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

        Schema::table('accept_bid', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accept_bid', function (Blueprint $table) {
            $table->dropColumn([
                'task_id',
                'order_id',
            ]);
        });
    }
};
