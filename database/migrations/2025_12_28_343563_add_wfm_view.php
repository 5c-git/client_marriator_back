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
        Schema::create('wfm_view_activities', function (Blueprint $table) {
            $table->id();
            $table->string('externalId')->nullable(false)->index();
            $table->text('name')->nullable();
            $table->tinyInteger('type')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfm_view_activities');
    }
};
