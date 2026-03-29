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
        $seting->name = 'Количество дней перед удалением не активного юзера не окончившего регистрацию';
        $seting->value = '50';
        $seting->key = 'delNotFinishUser';
        $seting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
