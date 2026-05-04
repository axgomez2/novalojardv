<?php

namespace Database\Seeders;

use App\Models\ProductType;
use App\Models\VinylStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Cria os ProductTypes oficiais da loja:
     *  - Discos Novos
     *  - Discos Usados
     *  - Discos Nacionais
     *  - Equipamentos
     *  - Acessórios
     *
     * Remove o ProductType legado 'vinil' (se existir e não tiver produtos).
     * Faz o backfill dos vinyl_stocks existentes baseado em is_new.
     *
     * Idempotente: pode ser executado várias vezes com segurança.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Discos Novos',
                'slug' => 'discos-novos',
                'icon' => 'disc',
                'is_active' => true,
            ],
            [
                'name' => 'Discos Usados',
                'slug' => 'discos-usados',
                'icon' => 'disc-2',
                'is_active' => true,
            ],
            [
                'name' => 'Discos Nacionais',
                'slug' => 'discos-nacionais',
                'icon' => 'flag',
                'is_active' => true,
            ],
            [
                'name' => 'Equipamentos',
                'slug' => 'equipamentos',
                'icon' => 'headphones',
                'is_active' => true,
            ],
            [
                'name' => 'Acessórios',
                'slug' => 'acessorios',
                'icon' => 'package',
                'is_active' => true,
            ],
        ];

        DB::transaction(function () use ($types) {
            foreach ($types as $data) {
                ProductType::updateOrCreate(
                    ['slug' => $data['slug']],
                    $data
                );
            }

            // Remove o ProductType legado 'vinil' (se existir e não tiver produtos vinculados)
            $legacy = ProductType::where('slug', 'vinil')->first();
            if ($legacy && $legacy->products()->count() === 0) {
                $legacy->delete();
            }

            // Backfill: stocks existentes sem product_type_id
            $novos = ProductType::where('slug', 'discos-novos')->first();
            $usados = ProductType::where('slug', 'discos-usados')->first();

            if ($novos) {
                VinylStock::whereNull('product_type_id')
                    ->where('is_new', true)
                    ->update(['product_type_id' => $novos->id]);
            }

            if ($usados) {
                VinylStock::whereNull('product_type_id')
                    ->where('is_new', false)
                    ->update(['product_type_id' => $usados->id]);
            }
        });
    }
}
