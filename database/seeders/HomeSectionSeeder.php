<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name' => 'Discos Novos',
                'slug' => 'discos-novos',
                'title' => 'Novidades',
                'subtitle' => 'Discos novos recém-chegados, prontos para entrega',
                'type' => 'discos_novos',
                'max_items' => 20,
                'sort_order' => 1,
                'is_active' => true,
                'view_all_link' => '/discos-novos',
            ],
            [
                'name' => 'Pré-Venda',
                'slug' => 'pre-venda',
                'title' => 'Pré-Venda',
                'subtitle' => 'Reserve agora e receba no lançamento',
                'type' => 'pre_venda',
                'max_items' => 20,
                'sort_order' => 2,
                'is_active' => true,
                'view_all_link' => '/discos-novos/pre-venda',
            ],
            [
                'name' => 'Discos Usados',
                'slug' => 'discos-usados',
                'title' => 'Discos Usados',
                'subtitle' => 'Raridades e clássicos de segunda mão com qualidade garantida',
                'type' => 'discos_usados',
                'max_items' => 20,
                'sort_order' => 3,
                'is_active' => true,
                'view_all_link' => '/discos-usados',
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::updateOrCreate(
                ['slug' => $section['slug']],
                $section
            );
        }
    }
}
