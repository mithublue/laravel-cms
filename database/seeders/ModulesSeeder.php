<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'pages', 'enabled' => true],
            ['name' => 'posts', 'enabled' => true],
            ['name' => 'news', 'enabled' => true],
            ['name' => 'products', 'enabled' => true],
        ];

        foreach ($defaults as $data) {
            Module::updateOrCreate(['name' => $data['name']], ['enabled' => $data['enabled']]);
        }
    }
}
