<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_favorite_workers', function (Blueprint $table) {
            $table->id();
            $table->string('external_worker_id')->index();
            $table->unsignedBigInteger('yandex_smena_site_id')->nullable()->index();
            $table->unsignedBigInteger('yandex_smena_profession_id')->nullable()->index();
            $table->boolean('is_favorite')->default(true);
            $table->timestamps();

            $table->unique(['external_worker_id', 'yandex_smena_site_id', 'yandex_smena_profession_id'], 'ys_favorite_worker_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_favorite_workers');
    }
};
