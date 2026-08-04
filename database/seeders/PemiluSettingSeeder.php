<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PemiluSettingSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = ['ppwp', 'gubernur', 'bupati', 'dpd', 'dpr_ri', 'dprd_prov', 'dprd_kab'];
        foreach ($jenis as $j) {
            \App\Models\PemiluSetting::updateOrCreate(
                ['jenis' => $j],
                ['is_active' => true]
            );
        }
    }
}
