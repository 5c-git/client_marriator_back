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
        Schema::create('directory_project', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name')->nullable(false);
        });

        Schema::create('directory_brand', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name')->nullable(false);
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('directory_counterparty', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name')->nullable(false);
            $table->string('inn')->nullable(false);
            $table->string('ogrn')->nullable(false);
            $table->text('legal_address')->nullable();
            $table->string('legal_email')->nullable();
        });

        Schema::create('directory_place', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('brand_id')->index()->nullable(false);
            $table->string('name')->nullable(false);
            $table->text('address_kladr')->nullable(false);
            $table->decimal('latitude',10,8)->nullable(false);
            $table->decimal('longitude',10,8)->nullable(false);
        });

        Schema::create('directory_brand_directory_counterparty', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->index()->nullable(false);
            $table->unsignedBigInteger('counterparty_id')->index()->nullable(false);
            $table->primary(['brand_id', 'counterparty_id']);
        });

        Schema::create('directory_project_directory_counterparty', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->index()->nullable(false);
            $table->unsignedBigInteger('counterparty_id')->index()->nullable(false);
            $table->primary(['project_id', 'counterparty_id']);
        });

        Schema::create('directory_project_directory_place', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->index()->nullable(false);
            $table->unsignedBigInteger('place_id')->index()->nullable(false);
            $table->primary(['project_id', 'place_id']);
        });

        Schema::create('directory_project_directory_view_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->index()->nullable(false);
            $table->unsignedBigInteger('view_activities_id')->index('idx_dp_project_view_activities_view_activities_id')->nullable(false);
            $table->primary(['project_id', 'view_activities_id']);
            $table->integer('price')->nullable(false);
        });

        //норма час ? Базовый норматив учета: Тонна
        //Универсальный эквивалент: Нормо-час
        //Минимальный объем услуги: 1 Тонна

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_project');
        Schema::dropIfExists('directory_brand');
        Schema::dropIfExists('directory_counterparty');
        Schema::dropIfExists('directory_place');
        Schema::dropIfExists('directory_brand_directory_counterparty');
        Schema::dropIfExists('directory_project_directory_counterparty');
        Schema::dropIfExists('directory_project_directory_place');
        Schema::dropIfExists('directory_project_directory_view_activities');
    }
};
