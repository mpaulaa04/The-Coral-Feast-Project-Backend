<?php
/**
 * Class InventorySeeder
 *
 * Seeds inventory categories and generates inventory items
 * for fish, plants, and supplements based on their models.
 */
namespace Database\Seeders;

use App\Models\Fish;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\Plant;
use App\Models\Supplement;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the inventory seeds.
     *
     * @return void
     */
    public function run(): void
    {
         // Define categories and ensure they exist.
        $categories = [
            'fish' => 'Peces',
            'plants' => 'Plantas',
            'supplements' => 'Suplementos',
        ];

        $categoryIds = [];

        foreach ($categories as $slug => $name) {
            $category = InventoryItemCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );

            $categoryIds[$slug] = $category->id;
        }
// --- FISH INVENTORY ITEMS ---
        $fishCategoryId = $categoryIds['fish'];

        Fish::all()->each(function (Fish $fish) use ($fishCategoryId): void {
            InventoryItem::updateOrCreate(
                ['slug' => 'fish-' . $fish->id],
                [
                    'inventory_item_category_id' => $fishCategoryId,
                    'name' => $fish->name,
                    'price' => $fish->price,
                    'image_path' => $fish->adult_image,
                    'pond_egg_image_path' => $fish->egg_image,
                    'pond_adult_image_path' => $fish->adult_image,
                    'pond_egg_dead_image_path' => $fish->egg_dead_image,
                    'pond_adult_dead_image_path' => $fish->adult_dead_image,
                    'fish_id' => $fish->id,
                    'plant_id' => null,
                    'supplement_id' => null,
                    'metadata' => [
                        'egg_stage_seconds' => $fish->egg_stage_seconds,
                        'juvenile_stage_seconds' => $fish->juvenile_stage_seconds,
                        'adult_stage_seconds' => $fish->adult_stage_seconds,
                    ],
                ]
            );
        });
// --- PLANTS ---
        $plants = [
            [
                'slug' => 'plant-red-algae',
                'name' => 'Red Algae',
                'price' => 60,
                'image' => './assets/img/redAlgae.svg',
                'oxygen_bonus' => 12,
                'ph_bonus' => 4,
                'health_regeneration' => 3,
                'effects' => [
                    'requires_fish' => true,
                    'growth_speed_multiplier' => 2,
                    'lifetime_seconds' => 30,
                ],
            ],
            [
                'slug' => 'plant-anubias',
                'name' => 'Elodea',
                'price' => 95,
                'image' => './assets/img/elodea.svg',
                'oxygen_bonus' => 8,
                'ph_bonus' => 6,
                'health_regeneration' => 2,
                'effects' => [
                    'requires_fish' => true,
                    'oxygen_protection' => true,
                    'lifetime_seconds' => 30,
                ],
            ],
            [
                'slug' => 'plant-musgo-java',
                'name' => 'Water Lettuce',
                'price' => 120,
                'image' => './assets/img/waterLettuce.svg',
                'oxygen_bonus' => 10,
                'ph_bonus' => 5,
                'health_regeneration' => 4,
                'effects' => [
                    'requires_fish' => true,
                    'temperature_protection' => true,
                    'lifetime_seconds' => 30,
                ],
            ],
        ];

        foreach ($plants as $plant) {
            $effects = $plant['effects'] ?? [];
 // Update/Create plant model
            $plantModel = Plant::updateOrCreate(
                ['slug' => $plant['slug']],
                [
                    'name' => $plant['name'],
                    'image_path' => $plant['image'],
                    'oxygen_bonus' => $plant['oxygen_bonus'],
                    'ph_bonus' => $plant['ph_bonus'],
                    'health_regeneration' => $plant['health_regeneration'],
                    'metadata' => [
                        'type' => 'plant',
                        'bonuses' => [
                            'oxygen' => $plant['oxygen_bonus'],
                            'ph' => $plant['ph_bonus'],
                            'health_regeneration' => $plant['health_regeneration'],
                        ],
                        'effects' => $effects,
                    ],
                ]
            );
// Add plant to inventory items
            InventoryItem::updateOrCreate(
                ['slug' => $plant['slug']],
                [
                    'inventory_item_category_id' => $categoryIds['plants'],
                    'name' => $plant['name'],
                    'price' => $plant['price'],
                    'image_path' => $plant['image'],
                    'plant_id' => $plantModel->id,
                    'fish_id' => null,
                    'supplement_id' => null,
                    'metadata' => [
                        'type' => 'plant',
                        'bonuses' => [
                            'oxygen' => $plant['oxygen_bonus'],
                            'ph' => $plant['ph_bonus'],
                            'health_regeneration' => $plant['health_regeneration'],
                        ],
                        'effects' => $effects,
                    ],
                ]
            );
        }
// --- SUPPLEMENTS ---
        $supplements = [
            [
                'slug' => 'supplement-fish-pellets',
                'name' => 'Fish Pellets',
                'price' => 80,
                'image' => './assets/img/fishPellets.svg',
                'health_boost' => 12,
                'hunger_reset' => true,
                'feeding_limit_bonus' => 1,
            ],
            [
                'slug' => 'supplement-fish-flakes',
                'name' => 'Fish Flakes',
                'price' => 180,
                'image' => './assets/img/hojuelaComida.svg',
                'health_boost' => 8,
                'hunger_reset' => false,
                'feeding_limit_bonus' => 0,
            ],
            [
                'slug' => 'supplement-color-bites',
                'name' => 'Color Bites',
                'price' => 70,
                'image' => './assets/img/colorBitesFish.svg',
                'health_boost' => 10,
                'hunger_reset' => true,
                'feeding_limit_bonus' => 0,
            ],
        ];

        foreach ($supplements as $supplement) {
            // Update/Create supplement model
            $supplementModel = Supplement::updateOrCreate(
                ['slug' => $supplement['slug']],
                [
                    'name' => $supplement['name'],
                    'image_path' => $supplement['image'],
                    'health_boost' => $supplement['health_boost'],
                    'hunger_reset' => $supplement['hunger_reset'],
                    'feeding_limit_bonus' => $supplement['feeding_limit_bonus'],
                    'metadata' => [
                        'type' => 'supplement',
                        'effects' => [
                            'health_boost' => $supplement['health_boost'],
                            'hunger_reset' => $supplement['hunger_reset'],
                            'feeding_limit_bonus' => $supplement['feeding_limit_bonus'],
                        ],
                    ],
                ]
            );
// Add supplement to inventory items
            InventoryItem::updateOrCreate(
                ['slug' => $supplement['slug']],
                [
                    'inventory_item_category_id' => $categoryIds['supplements'],
                    'name' => $supplement['name'],
                    'price' => $supplement['price'],
                    'image_path' => $supplement['image'],
                    'supplement_id' => $supplementModel->id,
                    'fish_id' => null,
                    'plant_id' => null,
                    'metadata' => [
                        'type' => 'supplement',
                        'effects' => [
                            'health_boost' => $supplement['health_boost'],
                            'hunger_reset' => $supplement['hunger_reset'],
                            'feeding_limit_bonus' => $supplement['feeding_limit_bonus'],
                        ],
                    ],
                ]
            );
        }
    }
}
