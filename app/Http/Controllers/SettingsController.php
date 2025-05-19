<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{

    public function index(): View
    {
        $settings = Setting::all();
        
        $user = auth()->user();

        return view('settings.index', compact('settings', 'user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($request->settings as $settingData) {
            Setting::where('key', $settingData['key'])
                ->update(['value' => $settingData['value']]);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully');
    }
}
