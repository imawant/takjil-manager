<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    /**
     * Update donor information
     */
    public function update(Request $request, Donor $donor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => ['required', 'string', 'regex:/^08[0-9]{9,11}$/'],
            'address' => 'nullable|string',
        ]);

        $donor->update($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'updated',
            "Mengubah info donatur {$donor->name}",
            'Donor',
            $donor->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Info donatur berhasil diperbarui'
        ]);
    }
}
