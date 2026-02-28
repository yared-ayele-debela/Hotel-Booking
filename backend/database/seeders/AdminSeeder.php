<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedAdmin();
        $this->seedVendor();
        $this->seedAdditionalVendors();
        $this->seedCustomer();
    }

    protected function seedSuperAdmin(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => Role::SUPER_ADMIN,
                'status' => 'active',
            ]
        );
        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }
        $admin->update(['role' => Role::SUPER_ADMIN, 'status' => 'active']);
    }

    protected function seedAdmin(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin2@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => Role::ADMIN,
                'status' => 'active',
            ]
        );
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
        $user->update(['role' => Role::ADMIN, 'status' => 'active']);
    }

    protected function seedVendor(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'vendor@test.com'],
            [
                'name' => 'Vendor User',
                'password' => bcrypt('password'),
                'role' => Role::VENDOR,
                'status' => 'active',
            ]
        );
        if (!$user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }
        $user->update(['role' => Role::VENDOR, 'status' => 'active']);
    }

    protected function seedAdditionalVendors(): void
    {
        $additionalVendors = [
            [
                'email' => 'vendor2@test.com',
                'name' => 'Sarah Johnson',
            ],
            [
                'email' => 'vendor3@test.com',
                'name' => 'Michael Chen',
            ],
            [
                'email' => 'vendor4@test.com',
                'name' => 'Emily Rodriguez',
            ],
            [
                'email' => 'vendor5@test.com',
                'name' => 'David Thompson',
            ],
            [
                'email' => 'vendor6@test.com',
                'name' => 'Lisa Anderson',
            ],
        ];

        foreach ($additionalVendors as $vendorData) {
            $user = User::firstOrCreate(
                ['email' => $vendorData['email']],
                [
                    'name' => $vendorData['name'],
                    'password' => bcrypt('password'),
                    'role' => Role::VENDOR,
                    'status' => 'active',
                ]
            );
            if (!$user->hasRole('vendor')) {
                $user->assignRole('vendor');
            }
            $user->update(['role' => Role::VENDOR, 'status' => 'active']);
        }
    }

    protected function seedCustomer(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Customer User',
                'password' => bcrypt('password'),
                'role' => Role::CUSTOMER,
                'status' => 'active',
            ]
        );
        if (!$user->hasRole('customer')) {
            $user->assignRole('customer');
        }
        $user->update(['role' => Role::CUSTOMER, 'status' => 'active']);
    }
}
