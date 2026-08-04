<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartaiProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $profiles = [
            [
                'slug' => 'pkb',
                'nama' => 'Partai Kebangkitan Bangsa',
                'nama_singkat' => 'PKB',
                'logo_path' => 'images/logo-pkb.png',
                'warna_utama' => '#008000',
                'warna_aksen' => '#006400',
                'nomor_urut_aktif' => 1,
                'nomor_urut_historis_json' => json_encode([2024 => 1]),
            ],
            [
                'slug' => 'gerindra',
                'nama' => 'Partai Gerakan Indonesia Raya',
                'nama_singkat' => 'Gerindra',
                'logo_path' => 'images/logo-gerindra.png',
                'warna_utama' => '#E63946',
                'warna_aksen' => '#A80010',
                'nomor_urut_aktif' => 2,
                'nomor_urut_historis_json' => json_encode([2024 => 2]),
            ],
            [
                'slug' => 'pdip',
                'nama' => 'Partai Demokrasi Indonesia Perjuangan',
                'nama_singkat' => 'PDI P',
                'logo_path' => 'images/logo-pdip.png',
                'warna_utama' => '#DC2626',
                'warna_aksen' => '#991B1B',
                'nomor_urut_aktif' => 3,
                'nomor_urut_historis_json' => json_encode([2024 => 3]),
            ],
            [
                'slug' => 'golkar',
                'nama' => 'Partai Golongan Karya',
                'nama_singkat' => 'Golkar',
                'logo_path' => 'images/logo-golkar.png',
                'warna_utama' => '#FBBF24',
                'warna_aksen' => '#D97706',
                'nomor_urut_aktif' => 4,
                'nomor_urut_historis_json' => json_encode([2024 => 4]),
            ],
            [
                'slug' => 'nasdem',
                'nama' => 'Partai NasDem',
                'nama_singkat' => 'NasDem',
                'logo_path' => 'images/logo-nasdem.png',
                'warna_utama' => '#0D47A1',
                'warna_aksen' => '#0A2F6F',
                'nomor_urut_aktif' => 5,
                'nomor_urut_historis_json' => json_encode([2024 => 5]),
            ],
            [
                'slug' => 'buruh',
                'nama' => 'Partai Buruh',
                'nama_singkat' => 'Buruh',
                'logo_path' => 'images/logo-buruh.png',
                'warna_utama' => '#FF6D00',
                'warna_aksen' => '#D50000',
                'nomor_urut_aktif' => 6,
                'nomor_urut_historis_json' => json_encode([2024 => 6]),
            ],
            [
                'slug' => 'gelora',
                'nama' => 'Partai Gelombang Rakyat Indonesia',
                'nama_singkat' => 'Gelora',
                'logo_path' => 'images/logo-gelora.png',
                'warna_utama' => '#00B0FF',
                'warna_aksen' => '#0091EA',
                'nomor_urut_aktif' => 7,
                'nomor_urut_historis_json' => json_encode([2024 => 7]),
            ],
            [
                'slug' => 'pks',
                'nama' => 'Partai Keadilan Sejahtera',
                'nama_singkat' => 'PKS',
                'logo_path' => 'images/logo-pks.png',
                'warna_utama' => '#FF9100',
                'warna_aksen' => '#FF6D00',
                'nomor_urut_aktif' => 8,
                'nomor_urut_historis_json' => json_encode([2024 => 8]),
            ],
            [
                'slug' => 'pkn',
                'nama' => 'Partai Kebangkitan Nusantara',
                'nama_singkat' => 'PKN',
                'logo_path' => 'images/logo-pkn.png',
                'warna_utama' => '#D50000',
                'warna_aksen' => '#9E0000',
                'nomor_urut_aktif' => 9,
                'nomor_urut_historis_json' => json_encode([2024 => 9]),
            ],
            [
                'slug' => 'hanura',
                'nama' => 'Partai Hati Nurani Rakyat',
                'nama_singkat' => 'Hanura',
                'logo_path' => 'images/logo-hanura.png',
                'warna_utama' => '#FF6F00',
                'warna_aksen' => '#E65100',
                'nomor_urut_aktif' => 10,
                'nomor_urut_historis_json' => json_encode([2024 => 10]),
            ],
            [
                'slug' => 'garuda',
                'nama' => 'Partai Garda Republik Indonesia',
                'nama_singkat' => 'Garuda',
                'logo_path' => 'images/logo-garuda.png',
                'warna_utama' => '#bb152c',
                'warna_aksen' => '#8a0f20',
                'nomor_urut_aktif' => 11,
                'nomor_urut_historis_json' => json_encode([2024 => 11]),
            ],
            [
                'slug' => 'pan',
                'nama' => 'Partai Amanat Nasional',
                'nama_singkat' => 'PAN',
                'logo_path' => 'images/logo-pan.png',
                'warna_utama' => '#2979FF',
                'warna_aksen' => '#2962FF',
                'nomor_urut_aktif' => 12,
                'nomor_urut_historis_json' => json_encode([2024 => 12]),
            ],
            [
                'slug' => 'pbb',
                'nama' => 'Partai Bulan Bintang',
                'nama_singkat' => 'PBB',
                'logo_path' => 'images/logo-pbb.png',
                'warna_utama' => '#1B5E20',
                'warna_aksen' => '#1b4d22',
                'nomor_urut_aktif' => 13,
                'nomor_urut_historis_json' => json_encode([2024 => 13]),
            ],
            [
                'slug' => 'demokrat',
                'nama' => 'Partai Demokrat',
                'nama_singkat' => 'Demokrat',
                'logo_path' => 'images/logo-demokrat.png',
                'warna_utama' => '#0D47A1',
                'warna_aksen' => '#0A2F6F',
                'nomor_urut_aktif' => 14,
                'nomor_urut_historis_json' => json_encode([2024 => 14]),
            ],
            [
                'slug' => 'psi',
                'nama' => 'Partai Solidaritas Indonesia',
                'nama_singkat' => 'PSI',
                'logo_path' => 'images/logo-psi.png',
                'warna_utama' => '#D50000',
                'warna_aksen' => '#C51162',
                'nomor_urut_aktif' => 15,
                'nomor_urut_historis_json' => json_encode([2024 => 15]),
            ],
            [
                'slug' => 'perindo',
                'nama' => 'Partai Persatuan Indonesia',
                'nama_singkat' => 'Perindo',
                'logo_path' => 'images/logo-perindo.png',
                'warna_utama' => '#0D47A1',
                'warna_aksen' => '#1A237E',
                'nomor_urut_aktif' => 16,
                'nomor_urut_historis_json' => json_encode([2024 => 16]),
            ],
            [
                'slug' => 'ppp',
                'nama' => 'Partai Persatuan Pembangunan',
                'nama_singkat' => 'PPP',
                'logo_path' => 'images/logo-ppp.png',
                'warna_utama' => '#1B5E20',
                'warna_aksen' => '#2E7D32',
                'nomor_urut_aktif' => 17,
                'nomor_urut_historis_json' => json_encode([2024 => 17]),
            ],
            [
                'slug' => 'ummat',
                'nama' => 'Partai Ummat',
                'nama_singkat' => 'Ummat',
                'logo_path' => 'images/logo-ummat.png',
                'warna_utama' => '#FFD600',
                'warna_aksen' => '#FFC400',
                'nomor_urut_aktif' => 18,
                'nomor_urut_historis_json' => json_encode([2024 => 18]),
            ],
        ];

        foreach ($profiles as $p) {
            $exists = DB::table('partai_profiles')
                ->where('slug', $p['slug'])
                ->exists();
            if (! $exists) {
                DB::table('partai_profiles')->insert(array_merge($p, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }
}
