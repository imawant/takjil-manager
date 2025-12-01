<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extract unique donors from donations table
        $uniqueDonors = DB::table('donations')
            ->select('donor_name', 'donor_whatsapp', 'donor_address')
            ->groupBy('donor_name', 'donor_whatsapp', 'donor_address')
            ->orderBy('donor_name')
            ->get();

        // Insert into donors table
        foreach ($uniqueDonors as $donor) {
            DB::table('donors')->insert([
                'name' => $donor->donor_name,
                'whatsapp' => $donor->donor_whatsapp,
                'address' => $donor->donor_address,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear donors table
        DB::table('donors')->truncate();
    }
};
