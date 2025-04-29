<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::query()->insert([
            ['name' => 'manager'],
            ['name' => 'recruiter'],
            ['name' => 'specialist'],
            ['name' => 'supervisor']
        ]);
        $userRole = Role::query()->where('name','user')->first();
        $userRole->name = 'client';
        $userRole->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::query()->whereIn(
            'name',
            ['manager','recruiter','specialist','supervisor']
        )->delete();

        $userRole = Role::query()->where('name','client')->first();
        $userRole->name = 'user';
        $userRole->save();
    }
};
