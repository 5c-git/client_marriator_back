<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_professions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('view_activity_id')->unique();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->string('status')->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->foreign('view_activity_id')->references('id')->on('directory_view_activities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_professions');
    }
};
