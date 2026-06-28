<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_id')->unique();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('directory_place')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_sites');
    }
};
