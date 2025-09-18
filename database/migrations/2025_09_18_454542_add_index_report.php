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

        Schema::table('report', function (Blueprint $table) {
            $table->dateTime('date_start')->index()->change();
            $table->dropIndex('report_order_id_index');
            $table->dropIndex('report_task_id_index');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
