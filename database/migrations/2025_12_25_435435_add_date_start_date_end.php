<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            // Удаляем старый уникальный индекс
            $table->dropUnique('version_type_unique');
            $table->dropColumn(['version']);
            // Добавляем новые поля
            $table->dateTime('date_start')->nullable(false);
            $table->dateTime('date_end')->nullable(false);

            // Создаем новый уникальный индекс
            $table->unique(['date_start', 'date_end', 'type'], 'date_range_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
