<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            LinguisticReferenceSeeder::class,
            CategorySeeder::class,
            PromptSeeder::class,
            ConsentVersionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
        ];

        if (! app()->environment('production')) {
            $seeders[] = DemoContributorSeeder::class;
        }

        $this->call($seeders);
    }
}
