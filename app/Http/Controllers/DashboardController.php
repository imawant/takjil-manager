<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ramadan 1447H starts approx Feb 18, 2026
        // Note: In a real app, we might want to allow configuring the start date.
        $startDate = \Carbon\Carbon::create(2026, 2, 18);
        $days = [];
        
        // Fetch all donations for the month to minimize queries
        // We fetch 30 days just to be safe
        $endDate = $startDate->copy()->addDays(29);
        $donations = \App\Models\Donation::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();

        for ($i = 1; $i <= 30; $i++) {
            $currentDate = $startDate->copy()->addDays($i - 1);
            $dateString = $currentDate->format('Y-m-d');
            $dailyDonations = $donations->where('date', $dateString);
            
            $days[] = [
                'hijri' => $i,
                'gregorian' => $currentDate->translatedFormat('d F Y'), // e.g. 18 Februari 2026
                'date' => $dateString,
                'day_name' => $currentDate->translatedFormat('l'), // e.g. Rabu
                'nasi_total' => $dailyDonations->where('type', 'nasi')->sum('quantity'),
                'snack_total' => $dailyDonations->where('type', 'snack')->sum('quantity'),
            ];
        }

        return view('dashboard', compact('days'));
    }
}
