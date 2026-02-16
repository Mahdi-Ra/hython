<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'name' => 'مدیر سیستم',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'department_id' => Department::query()->where('slug', 'admin')->value('id'),
            ]
        );

        $managers = [
            ['name' => 'علی مدیر', 'email' => 'manager1@demo.local', 'dept' => 'it'],
            ['name' => 'مریم مدیر', 'email' => 'manager2@demo.local', 'dept' => 'hr'],
        ];

        foreach ($managers as $m) {
            User::query()->firstOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_MANAGER,
                    'department_id' => Department::query()->where('slug', $m['dept'])->value('id'),
                ]
            );
        }

        $employees = [
            ['name' => 'رضا کارمند', 'email' => 'emp1@demo.local', 'dept' => 'it'],
            ['name' => 'سارا کارمند', 'email' => 'emp2@demo.local', 'dept' => 'it'],
            ['name' => 'امیر کارمند', 'email' => 'emp3@demo.local', 'dept' => 'hr'],
            ['name' => 'نرگس کارمند', 'email' => 'emp4@demo.local', 'dept' => 'finance'],
            ['name' => 'محمد کارمند', 'email' => 'emp5@demo.local', 'dept' => 'commerce'],
        ];

        foreach ($employees as $e) {
            User::query()->firstOrCreate(
                ['email' => $e['email']],
                [
                    'name' => $e['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_EMPLOYEE,
                    'department_id' => Department::query()->where('slug', $e['dept'])->value('id'),
                ]
            );
        }
    }
}
