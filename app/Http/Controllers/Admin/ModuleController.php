<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules with toggle controls.
     */
    public function index(Request $request): Response
    {
        $modules = Module::query()
            ->orderBy('name')
            ->get(['id','name','enabled'])
            ->map(fn(Module $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'enabled' => (bool) $m->enabled,
            ]);

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Update the specified module's enabled state.
     */
    public function update(Request $request, Module $module)
    {
        $data = $request->validate([
            'enabled' => ['required','boolean'],
        ]);

        $module->update($data);

        return back()->with('success', 'Module updated.');
    }
}
