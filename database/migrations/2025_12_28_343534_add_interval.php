<?php

use App\Models\Setting;
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
        $seting = new Setting();
        $seting->name = 'Нижний предел времени рабочего дня';
        $seting->value = '9:00';
        $seting->key = 'intervalDayStart';
        $seting->save();

        $seting = new Setting();
        $seting->name = 'Верхний предел времени рабочего дня';
        $seting->value = '21:00';
        $seting->key = 'intervalDayEnd';
        $seting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
