<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'فناوری اطلاعات', 'slug' => 'it', 'description' => 'واحد فناوری اطلاعات و پشتیبانی سامانه‌ها', 'parent_id' => null, 'is_active' => true],
            ['name' => 'منابع انسانی', 'slug' => 'hr', 'description' => 'امور کارکنان و منابع انسانی', 'parent_id' => null, 'is_active' => true],
            ['name' => 'مالی و حسابداری', 'slug' => 'finance', 'description' => 'امور مالی و حسابداری', 'parent_id' => null, 'is_active' => true],
            ['name' => 'بازرگانی', 'slug' => 'commerce', 'description' => 'امور بازرگانی و فروش', 'parent_id' => null, 'is_active' => true],
            ['name' => 'اداره کل', 'slug' => 'admin', 'description' => 'مدیریت و هماهنگی کل', 'parent_id' => null, 'is_active' => true],
        ];

        foreach ($departments as $data) {
            Department::query()->firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
