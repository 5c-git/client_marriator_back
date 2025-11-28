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
        Schema::create('user_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false)->index();
            $table->string('field')->nullable(false);
            $table->text('newData')->nullable(true);
            $table->text('oldData')->nullable(true);
            $table->unsignedSmallInteger('status')->nullable(false)->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_updates');
    }
};
