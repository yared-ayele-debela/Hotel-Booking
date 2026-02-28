<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            // User Permissions
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role Permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permission Permissions
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // Product Permissions
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Order Permissions
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
