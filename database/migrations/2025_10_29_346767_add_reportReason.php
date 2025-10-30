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
        Schema::create('report_reason', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id')->index()->nullable(false);
            $table->unsignedBigInteger('reason_id')->index()->nullable(false);
            $table->unsignedInteger('amount')->nullable(false)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_reason');
    }
};
