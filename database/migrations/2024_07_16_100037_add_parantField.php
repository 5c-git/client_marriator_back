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
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_citizenship', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_clothing_size', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_documentation', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_gender', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_hair_color', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_hair_length', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_height', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_messengers', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_offer_search', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_organization', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_region_of_residence', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_residence', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_shoe_size', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_tax_status', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_view_activities', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
        Schema::table('directory_weight', function (Blueprint $table) {
            $table->json('parentFields')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_organization');
    }
};
