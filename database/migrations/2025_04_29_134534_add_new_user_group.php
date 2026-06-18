<?php

use App\Models\User\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::query()->insertOrIgnore([
            ['name' => 'manager'],
            ['name' => 'recruiter'],
            ['name' => 'specialist'],
            ['name' => 'supervisor'],
        ]);
        $userRole = Role::query()->where('name', 'user')->first();
        if ($userRole !== null) {
            $userRole->name = 'client';
            $userRole->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::query()->whereIn(
            'name',
            ['manager', 'recruiter', 'specialist', 'supervisor']
        )->delete();

        $userRole = Role::query()->where('name', 'client')->first();
        if ($userRole !== null) {
            $userRole->name = 'user';
            $userRole->save();
        }
    }
};
