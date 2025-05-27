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
        Schema::create('accept_order', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->index()->nullable(false);
            $table->unsignedBigInteger('user_id')->index()->nullable(false);
            $table->primary(['order_id', 'user_id']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('place_id')->nullable(false)->index();
            $table->bigInteger('user_id')->nullable(false)->index();
            $table->bigInteger('accept_user_id')->nullable(false)->index();
            $table->bigInteger('specialist_user_id')->nullable(false)->index();
            $table->bigInteger('order_id')->nullable(false)->index();
            $table->bigInteger('bid_id')->nullable(false)->index();
            $table->smallInteger('status')->nullable(false)->default(1);
            $table->boolean('self_employed')->default(false);
            $table->decimal('price',10,2)->nullable(false);
            $table->decimal('income',10,2)->nullable(false);
            $table->decimal('scope_of_services',10,2)->nullable(false);

            $table->timestamps();
        });


        Schema::create('task_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->index()->nullable(false);
            $table->unsignedBigInteger('view_activity_id')->index()->nullable(false);
            $table->integer('count')->nullable(false);
            $table->dateTime('date_start')->nullable(false);
            $table->dateTime('date_end')->nullable(false);
            $table->boolean('need_foto')->nullable(false);
            $table->json('date_activity')->nullable(true);
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('place_id')->nullable(false)->index();
            $table->bigInteger('user_id')->nullable(false)->index();
            $table->bigInteger('accept_user_id')->nullable(false)->index();
            $table->bigInteger('supervisor_user_id')->nullable(false)->index();
            $table->bigInteger('order_id')->nullable(false)->index();
            $table->smallInteger('status')->nullable(false)->default(1);
            $table->boolean('self_employed')->default(false);
            $table->integer('radius')->nullable(false);
            $table->decimal('price',10,2)->nullable(false);
            $table->timestamps();
        });


        Schema::create('bid_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bid_id')->index()->nullable(false);
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
        Schema::dropIfExists('accept_order');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_activities');
        Schema::dropIfExists('bids');
        Schema::dropIfExists('bid_activities');
    }
};
