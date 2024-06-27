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
        Schema::table('directory_activities', function (Blueprint $table) {
            $table->string('detail_img')->nullable(true)->default('')->change();
            $table->string('link_text')->nullable(true)->default('')->change();
            $table->string('link')->nullable(true)->default('')->change();
            $table->string('type')->nullable(true)->default('external')->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directory_activities', function (Blueprint $table) {
            $table->string('detail_img')->nullable(false)->default('')->change();
            $table->string('link_text')->nullable(false)->default('')->change();
            $table->string('link')->nullable(false)->default('')->change();
            $table->string('type')->nullable(false)->default('external')->change();
        });
    }
};
