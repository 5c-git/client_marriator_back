<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_poll_state', function (Blueprint $table) {
            $table->id();
            $table->string('last_event_id')->nullable();
            $table->timestamp('polled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_poll_state');
    }
};
