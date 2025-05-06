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
        Schema::create('fields_user_role', function (Blueprint $table) {
            $table->unsignedBigInteger('field_id')->index()->nullable(false);
            $table->unsignedBigInteger('user_role_id')->index()->nullable(false);
            $table->primary(['field_id', 'user_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields_user_role');
    }
};
