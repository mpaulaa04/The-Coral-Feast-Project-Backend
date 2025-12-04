<?php
/**
 * Class MissionSeeder
 *
 * Seeds all missions with their multi-level configuration,
 * rewards, targets, and event keys used by the mission system.
 */
namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    use WithoutModelEvents;
  /**
     * Run the mission seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $defaultRewardImage = './assets/img/recompenza.png';
// All mission definitions with levels, rewards, and targets.
        $missions = [
            [
                'code' => 'pond_stock_first',
                'name' => 'Primer pez',
                'description' => 'Agrega tu primer pez al estanque',
                'event_key' => 'pond.stock',
                'target_amount' => 1,
                'reward' => 25,
                'sort_order' => 10,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 25],
                        ['target' => 3, 'reward' => 40],
                        ['target' => 5, 'reward' => 60],
                        ['target' => 8, 'reward' => 90],
                        ['target' => 12, 'reward' => 130],
                    ],
                ],
            ],
            [
                'code' => 'pond_clean_once',
                'name' => 'Agua limpia',
                'description' => 'Limpia el estanque una vez',
                'event_key' => 'pond.clean',
                'target_amount' => 1,
                'reward' => 40,
                'sort_order' => 20,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 40],
                        ['target' => 2, 'reward' => 60],
                        ['target' => 3, 'reward' => 80],
                        ['target' => 4, 'reward' => 110],
                        ['target' => 5, 'reward' => 150],
                    ],
                ],
            ],
            [
                'code' => 'pond_feed_three',
                'name' => 'Hora de comer',
                'description' => 'Alimenta a tus peces tres veces',
                'event_key' => 'pond.feed',
                'target_amount' => 3,
                'reward' => 60,
                'sort_order' => 30,
                'metadata' => [
                    'levels' => [
                        ['target' => 3, 'reward' => 60],
                        ['target' => 6, 'reward' => 80],
                        ['target' => 10, 'reward' => 110],
                        ['target' => 15, 'reward' => 150],
                        ['target' => 20, 'reward' => 200],
                    ],
                ],
            ],
            [
                'code' => 'pond_harvest_first',
                'name' => 'Primera cosecha',
                'description' => 'Cosecha tu primer pez adulto',
                'event_key' => 'pond.harvest',
                'target_amount' => 1,
                'reward' => 80,
                'sort_order' => 40,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 80],
                        ['target' => 2, 'reward' => 110],
                        ['target' => 4, 'reward' => 150],
                        ['target' => 6, 'reward' => 190],
                        ['target' => 8, 'reward' => 240],
                    ],
                ],
            ],
            [
                'code' => 'pond_harvest_three',
                'name' => 'Maestro cosechador',
                'description' => 'Cosecha tres peces adultos',
                'event_key' => 'pond.harvest',
                'target_amount' => 3,
                'reward' => 120,
                'sort_order' => 50,
                'metadata' => [
                    'levels' => [
                        ['target' => 3, 'reward' => 120],
                        ['target' => 6, 'reward' => 160],
                        ['target' => 10, 'reward' => 210],
                        ['target' => 14, 'reward' => 260],
                        ['target' => 20, 'reward' => 320],
                    ],
                ],
            ],
            [
                'code' => 'pond_solve_oxygen',
                'name' => 'Respira tranquilo',
                'description' => 'Soluciona un problema de oxígeno',
                'event_key' => 'pond.solve_oxygen',
                'target_amount' => 1,
                'reward' => 55,
                'sort_order' => 60,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 55],
                        ['target' => 2, 'reward' => 75],
                        ['target' => 3, 'reward' => 95],
                        ['target' => 4, 'reward' => 120],
                        ['target' => 5, 'reward' => 150],
                    ],
                ],
            ],
            [
                'code' => 'market_purchase_first',
                'name' => 'Cliente nuevo',
                'description' => 'Compra un artículo del mercado',
                'event_key' => 'market.purchase',
                'target_amount' => 1,
                'reward' => 35,
                'sort_order' => 70,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 35],
                        ['target' => 3, 'reward' => 55],
                        ['target' => 6, 'reward' => 80],
                        ['target' => 9, 'reward' => 110],
                        ['target' => 12, 'reward' => 150],
                    ],
                ],
            ],
            [
                'code' => 'market_purchase_fish',
                'name' => 'Nueva especie',
                'description' => 'Compra un pez en el mercado',
                'event_key' => 'market.purchase_fish',
                'target_amount' => 1,
                'reward' => 45,
                'sort_order' => 80,
                'metadata' => [
                    'levels' => [
                        ['target' => 1, 'reward' => 45],
                        ['target' => 2, 'reward' => 65],
                        ['target' => 4, 'reward' => 95],
                        ['target' => 6, 'reward' => 130],
                        ['target' => 9, 'reward' => 170],
                    ],
                ],
            ],
            [
                'code' => 'market_purchase_supplies',
                'name' => 'Almacena suplementos',
                'description' => 'Compra suplementos dos veces',
                'event_key' => 'market.purchase_supplies',
                'target_amount' => 2,
                'reward' => 70,
                'sort_order' => 90,
                'metadata' => [
                    'levels' => [
                        ['target' => 2, 'reward' => 70],
                        ['target' => 4, 'reward' => 95],
                        ['target' => 6, 'reward' => 120],
                        ['target' => 8, 'reward' => 150],
                        ['target' => 10, 'reward' => 190],
                    ],
                ],
            ],
            [
                'code' => 'market_purchase_five',
                'name' => 'Comprador frecuente',
                'description' => 'Realiza cinco compras en el mercado',
                'event_key' => 'market.purchase',
                'target_amount' => 5,
                'reward' => 90,
                'sort_order' => 100,
                'metadata' => [
                    'levels' => [
                        ['target' => 5, 'reward' => 90],
                        ['target' => 10, 'reward' => 130],
                        ['target' => 15, 'reward' => 180],
                        ['target' => 20, 'reward' => 240],
                        ['target' => 25, 'reward' => 310],
                    ],
                ],
            ],
        ];
// Seed or update missions
        foreach ($missions as $payload) {
            Mission::updateOrCreate(
                ['code' => $payload['code']],
                array_merge($payload, [
                    'reward_image_path' => $defaultRewardImage,
                    'is_repeatable' => false,
                ])
            );
        }
    }
}
