<?php
/**
 * Class NotificationTypeSeeder
 *
 * Seeds notification types used for styling in-game alerts.
 */

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the notification type seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Predefined notification types with UI styling.
        $types = [
            'default' => [
                'name' => 'General',
                'default_title' => 'Aviso',
                'background_color' => '#1A365D',
                'text_color' => '#FFFFFF',
                'border_color' => '#2A4365',
            ],
            'success' => [
                'name' => 'Correcto',
                'default_title' => 'Todo listo',
                'background_color' => '#2F855A',
                'text_color' => '#FFFFFF',
                'border_color' => '#22543D',
            ],
            'error' => [
                'name' => 'Error',
                'default_title' => 'Ocurrió un problema',
                'background_color' => '#C53030',
                'text_color' => '#FFFFFF',
                'border_color' => '#822727',
            ],
            'warning' => [
                'name' => 'Alerta',
                'default_title' => 'Atención',
                'background_color' => '#D69E2E',
                'text_color' => '#1A202C',
                'border_color' => '#B7791F',
            ],
            'market' => [
                'name' => 'Oferta de mercado',
                'default_title' => 'Oferta especial',
                'background_color' => '#6B46C1',
                'text_color' => '#FFFFFF',
                'border_color' => '#553C9A',
            ],
        ];
 // Seed or update each notification type.
        foreach ($types as $slug => $attributes) {
            NotificationType::query()->updateOrCreate(
                ['slug' => $slug],
                $attributes
            );
        }
    }
}
