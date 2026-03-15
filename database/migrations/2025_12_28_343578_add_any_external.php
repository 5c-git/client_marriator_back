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

        Schema::table('directory_view_activities', function (Blueprint $table) {
            $table->string('external_id_verme')->nullable(true)->index();
            $table->string('external_id_x5')->nullable(true)->index();
            $table->string('external_id_timeBook')->nullable(true)->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directory_view_activities', function (Blueprint $table) {
            $table->dropColumn([
                'external_id'
            ]);
        });
    }
};
