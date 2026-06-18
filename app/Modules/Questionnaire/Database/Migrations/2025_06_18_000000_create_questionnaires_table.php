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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->index();
            $table->string('status')->default(\Modules\Questionnaire\Enums\QuestionnaireStatus::PENDING->value)->index();
            $table->unsignedSmallInteger('current_step_index')->nullable();
            $table->string('current_step_class')->nullable();
            $table->json('data');
            $table->json('logs')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
