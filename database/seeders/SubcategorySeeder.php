<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('subcategories')->insert([
            [
                'name' => 'yoga',
                'slug' => 'yoga',
                'category_id' => 1,
                'status' => 'active'
            ],
            [
                'name' => 'strength',
                'slug' => 'strength',
                'category_id' => 1,
                'status' => 'active'
            ],
            [
                'name' => 'pilates',
                'slug' => 'pilates',
                'category_id' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'cardio',
                'slug' => 'cardio',
                'category_id' => 2,
                'status' => 'active'
            ]
        ]);
    }
}
