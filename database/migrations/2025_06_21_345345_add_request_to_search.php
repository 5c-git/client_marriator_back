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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('place_id')->nullable(false)->index();
            $table->bigInteger('user_id')->nullable(false)->index();
            $table->bigInteger('accept_user_id')->nullable(true)->index();
            $table->bigInteger('order_id')->nullable(true)->index();
            $table->smallInteger('status')->nullable(false)->default(1);
            $table->boolean('self_employed')->default(false);
            $table->integer('radius')->nullable(true);
            $table->decimal('price',10,2)->nullable(true);
            $table->bigInteger('task_id')->nullable(true)->index();
            $table->bigInteger('activity_id')->nullable(true)->index();
            $table->unsignedBigInteger('view_activity_id')->index()->nullable(false);
            $table->integer('count')->nullable(false);
            $table->dateTime('date_start')->nullable(false);
            $table->dateTime('date_end')->nullable(false);
            $table->boolean('need_foto')->nullable(false);
            $table->json('date_activity')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
