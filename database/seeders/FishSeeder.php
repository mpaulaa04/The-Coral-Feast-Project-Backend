<?php
/**
 * Class FishSeeder
 *
 * Seeds the fish table with predefined species and stats.
 */
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fish;

class FishSeeder extends Seeder
{
     /**
     * Run the fish seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $fishList = [
            [
                'name' => 'Trucha Arcoiris',
                'price' => 99,
                'egg_image' => './assets/img/huevo2.png',
                'adult_image' => './assets/img/pescado1.png',
                'egg_dead_image' => './assets/img/huevoM3.png',
                'adult_dead_image' => './assets/img/pezMuerto3.png',
                'oxygen_per_day' => 3,
                'ph_adjustment_per_day' => 1,
                'feedings_per_day' => 2,
                'egg_stage_seconds' => 120,
                'juvenile_stage_seconds' => 60,
                'adult_stage_seconds' => 180,
            ],
            [
                'name' => 'Tilapia Azul',
                'price' => 150,
                'egg_image' => './assets/img/huevo1.png',
                'adult_image' => './assets/img/pescado2.png',
                'egg_dead_image' => './assets/img/huevoM1.png',
                'adult_dead_image' => './assets/img/pezMuerto2.png',
                'oxygen_per_day' => 4,
                'ph_adjustment_per_day' => 1,
                'feedings_per_day' => 2,
                'egg_stage_seconds' => 120,
                'juvenile_stage_seconds' => 60,
                'adult_stage_seconds' => 180,
            ],
            [
                'name' => 'Pez Pargo',
                'price' => 200,
                'egg_image' => './assets/img/huevo3.png',
                'adult_image' => './assets/img/pescado3.png',
                'egg_dead_image' => './assets/img/huevoM3.png',
                'adult_dead_image' => './assets/img/pezMuerto1.png',
                'oxygen_per_day' => 5,
                'ph_adjustment_per_day' => 2,
                'feedings_per_day' => 3,
                'egg_stage_seconds' => 150,
                'juvenile_stage_seconds' => 75,
                'adult_stage_seconds' => 210,
            ],
            [
                'name' => 'Langostino',
                'price' => 350,
                'egg_image' => './assets/img/huevo4.png',
                'adult_image' => './assets/img/pescado4.svg',
                'egg_dead_image' => './assets/img/huevoM4.png',
                'adult_dead_image' => './assets/img/pezMuerto4.svg',
                'oxygen_per_day' => 6,
                'ph_adjustment_per_day' => 2,
                'feedings_per_day' => 3,
                'egg_stage_seconds' => 180,
                'juvenile_stage_seconds' => 90,
                'adult_stage_seconds' => 240,
            ],
        ];

        foreach ($fishList as $attributes) {
            Fish::updateOrCreate(
                ['name' => $attributes['name']],
                $attributes
            );
        }
    }
}
