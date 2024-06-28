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
        Schema::table('fields', function (Blueprint $table) {
            $table->text('helperInfo_text')->nullable()->change();
            $table->string('helperInfo_link')->nullable(true)->default('')->change();
            $table->string('helperInfo_link_text')->nullable(true)->default('')->change();
            $table->string('helperInfo_link_type')->nullable(true)->default('external')->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->text('helperInfo_text')->nullable()->change();
            $table->string('helperInfo_link')->nullable(false)->default('')->change();
            $table->string('helperInfo_link_text')->nullable(false)->default('')->change();
            $table->string('helperInfo_link_type')->nullable(false)->default('external')->change();
        });
    }
};
