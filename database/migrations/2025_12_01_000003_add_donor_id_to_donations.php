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
        // Step 1: Add donor_id column (nullable first)
        Schema::table('donations', function (Blueprint $table) {
            $table->unsignedBigInteger('donor_id')->nullable()->after('id');
        });

        // Step 2: Map donations to donors based on donor info
        $donations = DB::table('donations')->get();
        
        foreach ($donations as $donation) {
            $donor = DB::table('donors')
                ->where('name', $donation->donor_name)
                ->where('whatsapp', $donation->donor_whatsapp)
                ->where(function($query) use ($donation) {
                    $query->where('address', $donation->donor_address)
                          ->orWhere(function($q) use ($donation) {
                              $q->whereNull('address')
                                ->where(function($qq) use ($donation) {
                                    $qq->whereNull($donation->donor_address)
                                       ->orWhere($donation->donor_address, '');
                                });
                          });
                })
                ->first();
            
            if ($donor) {
                DB::table('donations')
                    ->where('id', $donation->id)
                    ->update(['donor_id' => $donor->id]);
            }
        }

        // Step 3: Make donor_id not nullable and add foreign key
        Schema::table('donations', function (Blueprint $table) {
            $table->unsignedBigInteger('donor_id')->nullable(false)->change();
            $table->foreign('donor_id')
                  ->references('id')
                  ->on('donors')
                  ->onDelete('cascade');
        });

        // Step 4: Drop old donor columns
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['donor_name', 'donor_whatsapp', 'donor_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Re-add old columns
        Schema::table('donations', function (Blueprint $table) {
            $table->string('donor_name')->nullable();
            $table->string('donor_whatsapp')->nullable();
            $table->text('donor_address')->nullable();
        });

        // Step 2: Copy data back from donors table
        $donations = DB::table('donations')
            ->join('donors', 'donations.donor_id', '=', 'donors.id')
            ->select('donations.id', 'donors.name', 'donors.whatsapp', 'donors.address')
            ->get();

        foreach ($donations as $donation) {
            DB::table('donations')
                ->where('id', $donation->id)
                ->update([
                    'donor_name' => $donation->name,
                    'donor_whatsapp' => $donation->whatsapp,
                    'donor_address' => $donation->address,
                ]);
        }

        // Step 3: Drop foreign key and donor_id column
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['donor_id']);
            $table->dropColumn('donor_id');
        });
    }
};
