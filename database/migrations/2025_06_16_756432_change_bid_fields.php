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

        Schema::table('bids', function (Blueprint $table) {
            $table->bigInteger('order_id')->nullable(true)->change();
            $table->bigInteger('accept_user_id')->nullable(true)->change();
            $table->bigInteger('task_id')->nullable(true)->change();
            $table->integer('radius')->nullable(true)->change();
            $table->decimal('price',10,2)->nullable(true)->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
