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

        Schema::table('report', function (Blueprint $table) {
            $table->string('placeholder')->default('час')->nullable(true);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
