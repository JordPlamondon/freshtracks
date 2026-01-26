<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->settings ?? []);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'show_live_revenue' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $currentSettings = $user->settings ?? [];
        $newSettings = array_merge($currentSettings, $validated);

        $user->update(['settings' => $newSettings]);

        return response()->json($newSettings);
    }
}
