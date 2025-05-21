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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('place_id')->nullable(false)->index();
            $table->bigInteger('user_id')->nullable(false)->index();
            $table->boolean('self_employed')->default(false);
            $table->timestamps();
        });


        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index()->nullable(false);
            $table->unsignedBigInteger('view_activity_id')->index()->nullable(false);
            $table->integer('count')->nullable(false);
            $table->dateTime('date_start')->nullable(false);
            $table->dateTime('date_end')->nullable(false);
            $table->boolean('need_foto')->nullable(false);
            $table->json('date_activity')->nullable(true);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_activities');
    }
};
