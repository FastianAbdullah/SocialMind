<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Platform;

class PlatformTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Platform::create([
            'name' => 'facebook',
        ]);
        Platform::create([
            'name' =>'instagram',
        ]);
        Platform::create([
            'name' => 'linkedin',
        ]);
    }
}
