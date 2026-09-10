<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Salutations', 'bi bi-chat-heart'],
            ['Présentation / identité', 'bi bi-person-badge'],
            ['Famille', 'bi bi-people'],
            ['Nombres', 'bi bi-123'],
            ['Temps / jours', 'bi bi-calendar3'],
            ['Nourriture', 'bi bi-cup-hot'],
            ['Maison', 'bi bi-house'],
            ['Marché / commerce', 'bi bi-shop'],
            ['Déplacements', 'bi bi-signpost'],
            ['École', 'bi bi-book'],
            ['Travail', 'bi bi-briefcase'],
        ];

        foreach ($categories as $order => [$name, $icon]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'display_order' => $order + 1, 'is_active' => true],
            );
        }
    }
}
