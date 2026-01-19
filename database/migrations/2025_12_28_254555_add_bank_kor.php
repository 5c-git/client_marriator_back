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
        Schema::table('directory_counterparty', function (Blueprint $table) {
            $table->string('bank_corr_account')->nullable(false);
            $table->string('bank_bic')->nullable(false);
            $table->string('okpo')->nullable(false);
            $table->string('okved')->nullable(false);
            $table->string('phone')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
