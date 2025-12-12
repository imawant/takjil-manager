<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Setting;

class TargetController extends Controller
{
    public function updateTargets(Request $request)
    {
        $validated = $request->validate([
            'target_nasi' => 'required|integer|min:1',
            'target_snack' => 'required|integer|min:1',
        ]);

        // Store targets in database
        Setting::set('target_nasi', $validated['target_nasi']);
        Setting::set('target_snack', $validated['target_snack']);

        return redirect()->back()->with('success', 'Target berhasil diperbarui!');
    }
}

