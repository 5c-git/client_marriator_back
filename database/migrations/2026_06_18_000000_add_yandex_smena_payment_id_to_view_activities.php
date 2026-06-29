<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directory_view_activities', function (Blueprint $table) {
            $table->foreignId('yandex_smena_payment_id')
                ->nullable()
                ->constrained('yandex_smena_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('directory_view_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yandex_smena_payment_id');
        });
    }
};
