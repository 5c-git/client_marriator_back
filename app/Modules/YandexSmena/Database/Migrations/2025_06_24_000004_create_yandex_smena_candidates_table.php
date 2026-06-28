<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yandex_smena_shift_id')->index();
            $table->string('external_worker_id')->index();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('inn', 12)->nullable();
            $table->string('snils', 14)->nullable();
            $table->string('status')->default('pending');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->foreign('yandex_smena_shift_id')->references('id')->on('yandex_smena_shifts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_candidates');
    }
};
