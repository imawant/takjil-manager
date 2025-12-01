<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'donor_name' => 'required|string|max:255',
            'donor_whatsapp' => ['required', 'string', 'regex:/^08[0-9]{9,11}$/'],
            'donor_address' => 'nullable|string',
            'type' => 'required|in:nasi,snack',
            'quantity' => 'required|integer|min:1',
            'date' => 'nullable|date|required_without:is_flexible_date',
            'is_flexible_date' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if (!empty($validated['is_flexible_date'])) {
            $validated['date'] = null;
        }

        $donation = \App\Models\Donation::create($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'created',
            "Menambah donasi {$donation->donor_name} - {$donation->type} {$donation->quantity} porsi",
            'Donation',
            $donation->id
        );

        return redirect()->back()->with('success', 'Donasi berhasil ditambahkan!');

    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Donation $donation)
    {
        $validated = $request->validate([
            'donor_name' => 'required|string|max:255',
            'donor_whatsapp' => ['required', 'string', 'regex:/^08[0-9]{9,11}$/'],
            'donor_address' => 'nullable|string',
            'type' => 'required|in:nasi,snack',
            'quantity' => 'required|integer|min:1',
            'date' => 'nullable|date|required_without:is_flexible_date',
            'is_flexible_date' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if (!empty($validated['is_flexible_date'])) {
            $validated['date'] = null;
        }

        $donation->update($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'updated',
            "Mengubah donasi {$donation->donor_name} - {$donation->type} {$donation->quantity} porsi",
            'Donation',
            $donation->id
        );

        return redirect()->back()->with('success', 'Donasi berhasil diperbarui!');
    }

    public function flexible(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Donation::where('is_flexible_date', true)
            ->whereNull('date');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_whatsapp', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->query('sort', 'quantity'); // Default sort by quantity
        $direction = $request->query('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->input('per_page', 10);
        if ($request->filled('search')) {
            $perPage = $request->input('per_page', 100000); // Show all if searching
        }
        
        if ($perPage == 'all') {
             $perPage = 100000;
        }

        $donations = $query->paginate($perPage)->withQueryString();
            
        return view('flexible', compact('donations'));
    }

    public function scheduleFlexible()
    {
        // 1. Get all donations (fixed and flexible) to calculate total daily average
        // Assuming Ramadan 2026 range
        $startDate = \Carbon\Carbon::create(2026, 2, 18);
        $endDate = $startDate->copy()->addDays(29);
        
        $allDonations = \App\Models\Donation::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orWhere(function($q) {
                $q->where('is_flexible_date', true)->whereNull('date');
            })
            ->get();

        $totalNasi = $allDonations->where('type', 'nasi')->sum('quantity');
        $totalSnack = $allDonations->where('type', 'snack')->sum('quantity');
        
        $avgNasi = ceil($totalNasi / 30);
        $avgSnack = ceil($totalSnack / 30);

        // 2. Get flexible donations to schedule (sorted by quantity desc)
        $flexibleNasi = \App\Models\Donation::where('is_flexible_date', true)
            ->whereNull('date')
            ->where('type', 'nasi')
            ->orderBy('quantity', 'desc')
            ->get();
            
        $flexibleSnack = \App\Models\Donation::where('is_flexible_date', true)
            ->whereNull('date')
            ->where('type', 'snack')
            ->orderBy('quantity', 'desc')
            ->get();

        // 3. Initialize daily loads
        $dailyNasi = [];
        $dailySnack = [];
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dailyNasi[$date] = $allDonations->where('date', $date)->where('type', 'nasi')->sum('quantity');
            $dailySnack[$date] = $allDonations->where('date', $date)->where('type', 'snack')->sum('quantity');
        }

        // 4. Distribute Nasi
        foreach ($flexibleNasi as $donation) {
            $this->assignToBestDay($donation, $dailyNasi, $avgNasi);
        }

        // 5. Distribute Snack
        foreach ($flexibleSnack as $donation) {
            $this->assignToBestDay($donation, $dailySnack, $avgSnack);
        }

        // Log activity
        $totalFlexible = $flexibleNasi->count() + $flexibleSnack->count();
        \App\Models\ActivityLog::log(
            'scheduled',
            "Menjadwalkan {$totalFlexible} donasi tanggal bebas ke kalender Ramadhan",
            'Donation',
            null
        );

        return redirect()->back()->with('success', 'Semua donasi tanggal bebas berhasil dijadwalkan!');
    }

    private function assignToBestDay($donation, &$dailyLoad, $limit)
    {
        // Sort days by current load (ascending)
        asort($dailyLoad);
        
        $assigned = false;
        
        // Try to find a day where adding this donation doesn't exceed the average limit
        foreach ($dailyLoad as $date => $load) {
            if ($load + $donation->quantity <= $limit) {
                $donation->update(['date' => $date]);
                $dailyLoad[$date] += $donation->quantity;
                $assigned = true;
                break;
            }
        }
        
        // If it doesn't fit anywhere under the limit, assign to the absolute lowest day
        if (!$assigned) {
            reset($dailyLoad); // Get the first key (lowest load)
            $bestDate = key($dailyLoad);
            $donation->update(['date' => $bestDate]);
            $dailyLoad[$bestDate] += $donation->quantity;
        }
    }

    public function destroy(\App\Models\Donation $donation)
    {
        $donorName = $donation->donor_name;
        $type = $donation->type;
        $quantity = $donation->quantity;
        $donationId = $donation->id;
        
        $donation->delete();
        
        // Log activity
        \App\Models\ActivityLog::log(
            'deleted',
            "Menghapus donasi {$donorName} - {$type} {$quantity} porsi",
            'Donation',
            $donationId
        );
        
        return redirect()->back()->with('success', 'Donasi berhasil dihapus!');
    }

    public function getDonors(\Illuminate\Http\Request $request)
    {
        $date = $request->query('date');
        $donations = \App\Models\Donation::where('date', $date)->get();
        return response()->json($donations);
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Donation::query();

        // Exclude flexible dates
        $query->where('is_flexible_date', false);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_whatsapp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Sorting
        $sort = $request->query('sort', 'date');
        $direction = $request->query('direction', 'asc');
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->input('per_page', 10);
        if ($request->filled('search')) {
            $perPage = $request->input('per_page', 100000); // Show all if searching
        }
        
        if ($perPage == 'all') {
             $perPage = 100000;
        }

        $donations = $query->paginate($perPage)->withQueryString();

        return view('search', compact('donations'));
    }

    public function recap(\Illuminate\Http\Request $request)
    {
        $sort = $request->query('sort', 'donor_name');
        $direction = $request->query('direction', 'asc');

        $query = \App\Models\Donation::selectRaw("donor_name, 
            MAX(donor_whatsapp) as donor_whatsapp,
            MAX(donor_address) as donor_address,
            SUM(CASE WHEN type = 'nasi' THEN quantity ELSE 0 END) as total_nasi,
            SUM(CASE WHEN type = 'snack' THEN quantity ELSE 0 END) as total_snack,
            SUM(quantity) as total_sumbangan")
            ->groupBy('donor_name');

        if ($request->filled('search')) {
            $search = $request->search;
            // $query->having('donor_name', 'like', "%{$search}%")
            //       ->orHaving('donor_whatsapp', 'like', "%{$search}%");
            $query->havingRaw("donor_name ILIKE ?", ["%{$search}%"])
                  ->orHavingRaw("MAX(donor_whatsapp) ILIKE ?", ["%{$search}%"]);
        }

        $donors = $query->orderBy($sort, $direction)->get();

        return view('recap', compact('donors'));
    }
    public function distribution()
    {
        // Ramadan 1447H starts approx Feb 18, 2026
        $startDate = \Carbon\Carbon::create(2026, 2, 18);
        $endDate = $startDate->copy()->addDays(29);

        // Get donations for the chart (only scheduled ones)
        $scheduledDonations = \App\Models\Donation::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();

        // Get ALL donations for average calculation (scheduled + unscheduled flexible)
        $allDonations = \App\Models\Donation::where(function($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function($q) {
                      $q->where('is_flexible_date', true)->whereNull('date');
                  });
        })->get();

        $totalNasi = $allDonations->where('type', 'nasi')->sum('quantity');
        $totalSnack = $allDonations->where('type', 'snack')->sum('quantity');
        
        $avgNasi = $totalNasi / 30;
        $avgSnack = $totalSnack / 30;

        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dateString = $currentDate->format('Y-m-d');
            
            $dailyDonations = $scheduledDonations->where('date', $dateString);

            $data[] = [
                'date' => $dateString,
                'total_nasi' => $dailyDonations->where('type', 'nasi')->sum('quantity'),
                'total_snack' => $dailyDonations->where('type', 'snack')->sum('quantity'),
            ];
        }

        return view('distribution', compact('data', 'avgNasi', 'avgSnack'));
    }

    public function getDonorDonations(\Illuminate\Http\Request $request)
    {
        $donorName = $request->query('donor_name');
        $donations = \App\Models\Donation::where('donor_name', $donorName)
            ->orderBy('date', 'asc')
            ->get();
        return response()->json($donations);
    }

    public function getDonorSuggestions(\Illuminate\Http\Request $request)
    {
        $query = $request->query('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $donors = \App\Models\Donation::select('donor_name', 'donor_whatsapp', 'donor_address')
            ->where(function($q) use ($query) {
                $q->where('donor_name', 'ilike', '%' . $query . '%')
                  ->orWhere('donor_whatsapp', 'ilike', '%' . $query . '%');
            })
            ->groupBy('donor_name', 'donor_whatsapp', 'donor_address')
            ->orderBy('donor_name', 'asc')
            ->limit(10)
            ->get();

        return response()->json($donors);
    }
}
