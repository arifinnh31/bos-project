<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = [
            ['nama_jasa' => 'Perawatan AC Inverter', 'harga_beli' => 70000, 'kategori_jasa' => 'Kebersihan', 'satuan_perhitungan' => 'unit', 'harga_jual' => 120000],
            ['nama_jasa' => 'Servis Motherboard Laptop', 'harga_beli' => 200000, 'kategori_jasa' => 'Teknologi', 'satuan_perhitungan' => 'unit', 'harga_jual' => 350000],
            ['nama_jasa' => 'Instalasi Jaringan Fiber Optik', 'harga_beli' => 150000, 'kategori_jasa' => 'Teknologi', 'satuan_perhitungan' => 'instalasi', 'harga_jual' => 300000],
            ['nama_jasa' => 'Fotografi Pernikahan Premium', 'harga_beli' => 500000, 'kategori_jasa' => 'Seni', 'satuan_perhitungan' => 'event', 'harga_jual' => 800000],
            ['nama_jasa' => 'Desain Logo Perusahaan Teknologi', 'harga_beli' => 200000, 'kategori_jasa' => 'Desain', 'satuan_perhitungan' => 'proyek', 'harga_jual' => 400000],
            ['nama_jasa' => 'Pemangkasan Bonsai', 'harga_beli' => 100000, 'kategori_jasa' => 'Kebersihan', 'satuan_perhitungan' => 'jam', 'harga_jual' => 200000],
            ['nama_jasa' => 'Penerjemahan Legal Dokumen', 'harga_beli' => 75000, 'kategori_jasa' => 'Bahasa', 'satuan_perhitungan' => 'lembar', 'harga_jual' => 150000],
            ['nama_jasa' => 'Penulisan Artikel SEO untuk Blog Teknologi', 'harga_beli' => 150000, 'kategori_jasa' => 'Penulisan', 'satuan_perhitungan' => 'artikel', 'harga_jual' => 300000],
            ['nama_jasa' => 'Editing Video Konten YouTube', 'harga_beli' => 250000, 'kategori_jasa' => 'Multimedia', 'satuan_perhitungan' => 'proyek', 'harga_jual' => 500000],
            ['nama_jasa' => 'Optimasi SEO untuk Website E-Commerce', 'harga_beli' => 400000, 'kategori_jasa' => 'Digital Marketing', 'satuan_perhitungan' => 'proyek', 'harga_jual' => 700000],
        ];

        foreach ($service as $s) {
            Service::create($s);
        }
    }
}
