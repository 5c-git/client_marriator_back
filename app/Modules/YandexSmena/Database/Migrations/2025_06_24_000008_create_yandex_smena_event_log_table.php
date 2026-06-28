<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_smena_event_log', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->string('direction'); // out | in
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->string('event_ts');
            $table->json('payload');
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->string('source_event_id')->nullable()->unique();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['event_type', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::table('yandex_smena_event_log', function (Blueprint $table) {
            $table->dropColumn(['event_ts', 'response', 'error', 'source_event_id']);
        });

        Schema::dropIfExists('yandex_smena_event_log');
    }
};
