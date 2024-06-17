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
            $table->text('preview_text')->nullable();
            $table->text('detail_name')->nullable();
            $table->text('detail_text')->nullable();
            $table->string('detail_img')->nullable(false)->default('');
            $table->string('link_text')->nullable(false)->default('');
            $table->string('link')->nullable(false)->default('');
            $table->string('type')->nullable(false)->default('external');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directory_activities', function (Blueprint $table) {
            $table->dropColumn(['preview_text']);
            $table->dropColumn(['detail_name']);
            $table->dropColumn(['detail_text']);
            $table->dropColumn(['detail_img']);
            $table->dropColumn(['link_text']);
            $table->dropColumn(['link']);
            $table->dropColumn(['type']);
        });
    }
};
