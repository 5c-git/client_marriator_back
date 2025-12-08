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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false)->default('');
            $table->tinyInteger('type')->nullable(false);
            $table->decimal('version', 6, 2)->nullable(false);
            $table->string('template')->nullable(false);
            $table->unique(['version', 'type'], 'version_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
