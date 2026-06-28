<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yandex_smena_sites', function (Blueprint $table) {
            $table->dropColumn(['status', 'sync_error', 'last_sync_at']);
            $table->string('external_id')->nullable()->change();
        });

        Schema::table('yandex_smena_professions', function (Blueprint $table) {
            $table->dropColumn(['status', 'sync_error', 'last_sync_at']);
            $table->string('external_id')->nullable()->change();
        });

        Schema::table('yandex_smena_payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'sync_error', 'last_sync_at']);
            $table->string('external_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('yandex_smena_sites', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('longitude');
            $table->text('sync_error')->nullable()->after('status');
            $table->timestamp('last_sync_at')->nullable()->after('sync_error');
            $table->string('external_id')->nullable(false)->change();
        });

        Schema::table('yandex_smena_professions', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('name');
            $table->text('sync_error')->nullable()->after('status');
            $table->timestamp('last_sync_at')->nullable()->after('sync_error');
            $table->string('external_id')->nullable(false)->change();
        });

        Schema::table('yandex_smena_payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('currency');
            $table->text('sync_error')->nullable()->after('status');
            $table->timestamp('last_sync_at')->nullable()->after('sync_error');
            $table->string('external_id')->nullable(false)->change();
        });
    }
};
