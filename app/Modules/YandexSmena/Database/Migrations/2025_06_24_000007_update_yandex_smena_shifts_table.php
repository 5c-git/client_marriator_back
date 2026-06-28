<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yandex_smena_shifts', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->string('external_id')->nullable()->change();

            $table->string('entity_id')->unique()->after('id');
            $table->timestamp('published_at')->nullable()->after('yandex_smena_payment_id');
            $table->timestamp('last_poll_at')->nullable()->after('published_at');
            $table->string('last_source_event_id')->nullable()->after('last_poll_at');
        });
    }

    public function down(): void
    {
        Schema::table('yandex_smena_shifts', function (Blueprint $table) {
            $table->dropColumn(['entity_id', 'published_at', 'last_poll_at', 'last_source_event_id']);
            $table->string('external_id')->nullable(false)->unique()->change();
        });
    }
};
