<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ThemeManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Themes/Index', [
            'active' => ThemeManager::active(),
            'available' => ThemeManager::available(),
        ]);
    }

    public function activate(Request $request)
    {
        $data = $request->validate([
            'theme' => ['required', 'string'],
        ]);

        $available = ThemeManager::available();
        if (!in_array($data['theme'], $available, true)) {
            return back()->withErrors(['theme' => 'Theme not found.']);
        }

        ThemeManager::setActive($data['theme']);

        return back()->with('success', 'Theme activated: ' . $data['theme']);
    }
}
