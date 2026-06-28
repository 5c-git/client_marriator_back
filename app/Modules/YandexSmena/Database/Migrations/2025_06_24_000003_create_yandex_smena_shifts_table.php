<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('order_activity_id')->nullable()->index();
            $table->unsignedBigInteger('task_activity_id')->nullable()->index();
            $table->unsignedBigInteger('yandex_smena_site_id');
            $table->unsignedBigInteger('yandex_smena_profession_id');
            $table->unsignedBigInteger('yandex_smena_payment_id');
            $table->string('external_id')->nullable()->unique();
            $table->string('external_status')->nullable();
            $table->timestamp('start_at');
            $table->unsignedInteger('length_min');
            $table->unsignedInteger('rest_length_min')->default(0);
            $table->json('payload');
            $table->json('response')->nullable();
            $table->text('sync_error')->nullable();
            $table->timestamps();

            $table->foreign('yandex_smena_site_id')->references('id')->on('yandex_smena_sites');
            $table->foreign('yandex_smena_profession_id')->references('id')->on('yandex_smena_professions');
            $table->foreign('yandex_smena_payment_id')->references('id')->on('yandex_smena_payments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_shifts');
    }
};
