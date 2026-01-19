<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leave_types')->insertOrIgnore([
            [
                'name' => 'Vakantie',
                'description' => 'Regulier betaald verlof.',
                'requires_proof' => false,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ziek',
                'description' => 'Ziekmelding van werknemer.',
                'requires_proof' => false,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bijzonder verlof',
                'description' => 'Kort buitengewoon verlof (bijv. huwelijk of verhuizing).',
                'requires_proof' => false,
                'paid' => false,
                'max_days' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zwangerschapsverlof',
                'description' => 'Wettelijk zwangerschapsverlof.',
                'requires_proof' => true,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bevallingsverlof',
                'description' => 'Wettelijk bevallingsverlof na geboorte.',
                'requires_proof' => true,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Geboorteverlof',
                'description' => 'Verlof voor partner bij geboorte.',
                'requires_proof' => false,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Adoptie- en pleegzorgverlof',
                'description' => 'Verlof bij adoptie of pleegzorg.',
                'requires_proof' => true,
                'paid' => true,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ouderschapsverlof',
                'description' => 'Langdurig verlof voor ouders.',
                'requires_proof' => false,
                'paid' => false,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zorgverlof',
                'description' => 'Verlof voor het zorgen van een naaste.',
                'requires_proof' => true,
                'paid' => false,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Onbetaald verlof',
                'description' => 'Onbetaald verlof voor studie of lange reis.',
                'requires_proof' => false,
                'paid' => false,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
