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

        Schema::table('bids', function (Blueprint $table) {
            $table->unsignedBigInteger('view_activity_id')->index()->nullable(false);
            $table->integer('count')->nullable(false);
            $table->dateTime('date_start')->nullable(false);
            $table->dateTime('date_end')->nullable(false);
            $table->boolean('need_foto')->nullable(false);
            $table->json('date_activity')->nullable(true);
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn(['supervisor_user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
