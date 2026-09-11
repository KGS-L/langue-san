<?php

namespace Database\Seeders;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\DemoContributorSeeder;
use Database\Seeders\LinguisticReferenceSeeder;
use Database\Seeders\PromptSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LinguisticReferenceSeeder::class,
            CategorySeeder::class,
            PromptSeeder::class,
            ConsentVersionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            DemoContributorSeeder::class,
        ]);
    }
}
