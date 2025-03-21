<?php

namespace Database\Seeders;

use App\Models\Policies;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PoliciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Policies::firstOrCreate([
            'title' => 'Políticas de la Empresa'
        ], [
            'description' => 'Estas son las políticas de la empresa'
        ]);
    }
}
