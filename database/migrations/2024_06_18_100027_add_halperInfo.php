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
            $table->text('helperInfo_text')->nullable();
            $table->string('helperInfo_link')->nullable(false)->default('');
            $table->string('helperInfo_link_text')->nullable(false)->default('');
            $table->string('helperInfo_link_type')->nullable(false)->default('external');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn(['helperInfo_text']);
            $table->dropColumn(['helperInfo_link']);
            $table->dropColumn(['helperInfo_link_text']);
            $table->dropColumn(['helperInfo_link_type']);
        });
    }
};
