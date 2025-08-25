<?php
// Fichier: database/seeders/PlanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Illuminate\Support\Facades\Schema;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Plan::truncate();
        Schema::enableForeignKeyConstraints();

        // Création du plan mensuel
        Plan::create([
            'name' => 'Premium Mensuel',
            'description' => 'Idéal pour démarrer et tester le marché.',
            'price' => 1000.00,
            'currency' => 'xof',
            'interval' => 'month',
            'stripe_price_id' => 'price_remplacer_par_id_mensuel',
            'features' => json_encode([
                'Publication de 10 produits en ligne',
                'Publicité illimitée',
            ]),
        ]);

        Plan::create([
            'name' => 'Premium Mensuel',
            'description' => 'Idéal pour démarrer et tester le marché.',
            'price' => 3000.00,
            'currency' => 'xof',
            'interval' => 'month',
            'stripe_price_id' => 'price_remplacer_par_id_mensuel',
            'features' => json_encode([
                'Publication de produits illimitée',
                'Publicité illimitée',
            ]),
        ]);

        // Création du plan annuel
        Plan::create([
            'name' => 'Premium Annuel',
            'description' => 'Économisez avec un engagement à l\'année et accédez à des outils avancés.',
            'price' => 15000.00,
            'currency' => 'xof',
            'interval' => 'year',
            'stripe_price_id' => 'price_remplacer_par_id_annuel',
            'features' => json_encode([
                'Publication de produits illimitée',
                'Publicité illimitée',
                'Outils de comptabilité simplifiée', // <-- L'avantage clé
            ]),
        ]);
    }
}