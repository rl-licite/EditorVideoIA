<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::where('user_id', Auth::id())->pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => ['nullable', 'string', 'max:100'],
            'default_watermark' => ['nullable', 'string', 'max:100'],
            'max_parallel_jobs' => ['nullable', 'integer', 'min:1', 'max:4'],
            'default_resolution' => ['nullable', 'string', 'max:20'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['user_id' => Auth::id(), 'key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('settings.index')->with('success', 'Configurações salvas.');
    }
}
