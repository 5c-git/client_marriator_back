<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->unsignedInteger('amount_per_hour')->nullable();
            $table->string('currency', 3)->default('RUB');
            $table->string('status')->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_smena_payments');
    }
};
