<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            DB::table('permissions')->insert([
                ['id' => 1, 'name' => 'web_insert', 'guard_name' => 'web'],
                ['id' => 2, 'name' => 'web_update', 'guard_name' => 'web'],
                ['id' => 3, 'name' => 'web_delete', 'guard_name' => 'web'],
                ['id' => 4, 'name' => 'web_view', 'guard_name' => 'web'],
                ['id' => 5, 'name' => 'api_insert', 'guard_name' => 'api'],
                ['id' => 6, 'name' => 'api_update', 'guard_name' => 'api'],
                ['id' => 7, 'name' => 'api_delete', 'guard_name' => 'api'],
                ['id' => 8, 'name' => 'api_view', 'guard_name' => 'api'],
            ]);

            DB::table('roles')->insert([
                ['id' => 1, 'name' => 'developer', 'guard_name' => 'web'],
                ['id' => 2, 'name' => 'admin', 'guard_name' => 'web'],
                ['id' => 3, 'name' => 'retailer', 'guard_name' => 'api'],
                ['id' => 4, 'name' => 'customer', 'guard_name' => 'api'],
            ]);

            DB::table('users')->insert([
                ['id' => 1, 'name' => 'developer', 'email' => 'developer@developer.com', 'password' => Hash::make('12345678'), 'otp_verified_at' => now()],
                ['id' => 2, 'name' => 'Admin', 'email' => 'admin@admin.com', 'password' => Hash::make('12345678'), 'otp_verified_at' => now()],
                ['id' => 3, 'name' => 'Retailer', 'email' => 'retailer@retailer.com', 'password' => Hash::make('12345678'), 'otp_verified_at' => now()],
                ['id' => 4, 'name' => 'Customer', 'email' => 'customer@customer.com', 'password' => Hash::make('12345678'), 'otp_verified_at' => now()]
            ]);

            DB::table('role_has_permissions')->insert([
                ['permission_id' => 1, 'role_id' => 1],
                ['permission_id' => 2, 'role_id' => 1],
                ['permission_id' => 3, 'role_id' => 1],
                ['permission_id' => 4, 'role_id' => 1],
                ['permission_id' => 1, 'role_id' => 2],
                ['permission_id' => 2, 'role_id' => 2],
                ['permission_id' => 3, 'role_id' => 2],
                ['permission_id' => 4, 'role_id' => 2],
                ['permission_id' => 5, 'role_id' => 3],
                ['permission_id' => 6, 'role_id' => 3],
                ['permission_id' => 7, 'role_id' => 3],
                ['permission_id' => 8, 'role_id' => 3],
                ['permission_id' => 5, 'role_id' => 4],
                ['permission_id' => 6, 'role_id' => 4],
                ['permission_id' => 7, 'role_id' => 4],
                ['permission_id' => 8, 'role_id' => 4],
            ]);

            DB::table('model_has_roles')->insert([
                ['role_id' => 1, 'model_id' => 1, 'model_type' => 'App\Models\User'],
                ['role_id' => 2, 'model_id' => 2, 'model_type' => 'App\Models\User'],
                ['role_id' => 3, 'model_id' => 3, 'model_type' => 'App\Models\User'],
                ['role_id' => 4, 'model_id' => 4, 'model_type' => 'App\Models\User']
            ]);

            DB::table('model_has_permissions')->insert([
                ['permission_id' => 1, 'model_id' => 1, 'model_type' => 'App\Models\User'],
                ['permission_id' => 2, 'model_id' => 1, 'model_type' => 'App\Models\User'],
                ['permission_id' => 3, 'model_id' => 1, 'model_type' => 'App\Models\User'],
                ['permission_id' => 4, 'model_id' => 1, 'model_type' => 'App\Models\User'],
                ['permission_id' => 1, 'model_id' => 2, 'model_type' => 'App\Models\User'],
                ['permission_id' => 2, 'model_id' => 2, 'model_type' => 'App\Models\User'],
                ['permission_id' => 3, 'model_id' => 2, 'model_type' => 'App\Models\User'],
                ['permission_id' => 4, 'model_id' => 2, 'model_type' => 'App\Models\User'],
                ['permission_id' => 5, 'model_id' => 3, 'model_type' => 'App\Models\User'],
                ['permission_id' => 6, 'model_id' => 3, 'model_type' => 'App\Models\User'],
                ['permission_id' => 7, 'model_id' => 3, 'model_type' => 'App\Models\User'],
                ['permission_id' => 8, 'model_id' => 3, 'model_type' => 'App\Models\User'],
                ['permission_id' => 5, 'model_id' => 4, 'model_type' => 'App\Models\User'],
                ['permission_id' => 6, 'model_id' => 4, 'model_type' => 'App\Models\User'],
                ['permission_id' => 7, 'model_id' => 4, 'model_type' => 'App\Models\User'],
                ['permission_id' => 8, 'model_id' => 4, 'model_type' => 'App\Models\User'],
            ]);
        }
    }
}