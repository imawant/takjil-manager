<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users
        \App\Models\User::create([
            'name' => 'Admin Takjil',
            'email' => 'admin@takjil.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'Petugas Takjil',
            'email' => 'petugas@takjil.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        \App\Models\User::create([
            'name' => 'Guest User',
            'email' => 'guest@takjil.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
        ]);

        // Create Dummy Donations
        // Assuming Ramadan 2026 starts around Feb 18, 2026
        $startDate = \Carbon\Carbon::create(2026, 2, 18);
        
        // Define donors with consistent data
        $donors = [
            ['name' => 'Budi Santoso', 'address' => 'Jl. Merdeka No. 12', 'whatsapp' => '081234567890'],
            ['name' => 'Siti Aminah', 'address' => 'Jl. Sudirman No. 45', 'whatsapp' => '081234567891'],
            ['name' => 'Rahmat Hidayat', 'address' => 'Jl. Ahmad Yani No. 7', 'whatsapp' => '081234567892'],
            ['name' => 'Dewi Sartika', 'address' => 'Jl. Diponegoro No. 23', 'whatsapp' => '081234567893'],
            ['name' => 'Agus Setiawan', 'address' => 'Jl. Pahlawan No. 56', 'whatsapp' => '081234567894'],
            ['name' => 'Ratna Sari', 'address' => 'Jl. Melati No. 33', 'whatsapp' => '081234567895'],
            ['name' => 'Bambang Pamungkas', 'address' => 'Jl. Mawar No. 88', 'whatsapp' => '081234567896'],
            ['name' => 'Sri Wahyuni', 'address' => 'Jl. Kenanga No. 15', 'whatsapp' => '081234567897'],
            ['name' => 'Eko Prasetyo', 'address' => 'Komplek Griya Indah Blok A12', 'whatsapp' => '081234567898'],
            ['name' => 'Nurul Hidayah', 'address' => 'Perumahan Damai Sentosa No. 77', 'whatsapp' => '081234567899'],
            ['name' => 'Hendra Gunawan', 'address' => 'Jl. Merdeka No. 99', 'whatsapp' => '082345678901'],
            ['name' => 'Yuni Astuti', 'address' => 'Jl. Sudirman No. 21', 'whatsapp' => '082345678902'],
            ['name' => 'Dedi Supriadi', 'address' => 'Jl. Ahmad Yani No. 44', 'whatsapp' => '082345678903'],
            ['name' => 'Lilis Suryani', 'address' => 'Jl. Diponegoro No. 66', 'whatsapp' => '082345678904'],
            ['name' => 'Iwan Fals', 'address' => 'Jl. Pahlawan No. 11', 'whatsapp' => '082345678905'],
            ['name' => 'Rina Marlina', 'address' => 'Jl. Melati No. 55', 'whatsapp' => '082345678906'],
            ['name' => 'Joko Widodo', 'address' => 'Jl. Mawar No. 22', 'whatsapp' => '082345678907'],
            ['name' => 'Megawati', 'address' => 'Jl. Kenanga No. 88', 'whatsapp' => '082345678908'],
            ['name' => 'Susilo Bambang', 'address' => 'Komplek Griya Indah Blok B5', 'whatsapp' => '082345678909'],
            ['name' => 'Prabowo Subianto', 'address' => 'Perumahan Damai Sentosa No. 34', 'whatsapp' => '082345678910'],
            ['name' => 'Anies Baswedan', 'address' => 'Jl. Merdeka No. 67', 'whatsapp' => '083456789012'],
            ['name' => 'Ganjar Pranowo', 'address' => 'Jl. Sudirman No. 90', 'whatsapp' => '083456789013'],
            ['name' => 'Ridwan Kamil', 'address' => 'Jl. Ahmad Yani No. 13', 'whatsapp' => '083456789014'],
            ['name' => 'Sandiaga Uno', 'address' => 'Jl. Diponegoro No. 76', 'whatsapp' => '083456789015'],
            ['name' => 'Erick Thohir', 'address' => 'Jl. Pahlawan No. 29', 'whatsapp' => '083456789016'],
            ['name' => 'Najwa Shihab', 'address' => 'Jl. Melati No. 41', 'whatsapp' => '083456789017'],
            ['name' => 'Raffi Ahmad', 'address' => 'Jl. Mawar No. 63', 'whatsapp' => '083456789018'],
            ['name' => 'Nagita Slavina', 'address' => 'Jl. Kenanga No. 19', 'whatsapp' => '083456789019'],
            ['name' => 'Deddy Corbuzier', 'address' => 'Komplek Griya Indah Blok C8', 'whatsapp' => '083456789020'],
            ['name' => 'Atta Halilintar', 'address' => 'Perumahan Damai Sentosa No. 52', 'whatsapp' => '083456789021'],
            ['name' => 'Hamba Allah', 'address' => 'Tidak Diketahui', 'whatsapp' => '081111111111'],
        ];

        $descriptions = [
            'Semoga berkah', 'Untuk berbuka puasa', 'Sedekah Ramadhan', 'Hamba Allah', 
            'Titipan dari keluarga', 'Semoga bermanfaat', '', 'Mohon doanya'
        ];

        // Generate Nasi Donations (Target ~3500 total, range 10-50, avg 30)
        // 117 * 30 = 3510
        for ($i = 0; $i < 117; $i++) {
            $donor = $donors[array_rand($donors)];
            $isFlexible = rand(1, 100) <= 30;

            \App\Models\Donation::create([
                'donor_name' => $donor['name'],
                'donor_address' => $donor['address'],
                'donor_whatsapp' => $donor['whatsapp'],
                'type' => 'nasi',
                'quantity' => rand(10, 50),
                'date' => $isFlexible ? null : $startDate->copy()->addDays(rand(0, 29)),
                'is_flexible_date' => $isFlexible,
                'description' => $descriptions[array_rand($descriptions)],
            ]);
        }

        // Generate Snack Donations (Target ~6000 total, range 10-50, avg 30)
        // 200 * 30 = 6000
        for ($i = 0; $i < 200; $i++) {
            $donor = $donors[array_rand($donors)];
            $isFlexible = rand(1, 100) <= 30;

            \App\Models\Donation::create([
                'donor_name' => $donor['name'],
                'donor_address' => $donor['address'],
                'donor_whatsapp' => $donor['whatsapp'],
                'type' => 'snack',
                'quantity' => rand(10, 50),
                'date' => $isFlexible ? null : $startDate->copy()->addDays(rand(0, 29)),
                'is_flexible_date' => $isFlexible,
                'description' => $descriptions[array_rand($descriptions)],
            ]);
        }
    }
}
