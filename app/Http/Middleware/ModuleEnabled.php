<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleEnabled
{
    /**
     * Ensure the given module is enabled, otherwise abort 404.
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $enabled = Module::where('name', $module)->value('enabled');

        if (!$enabled) {
            abort(404);
        }

        return $next($request);
    }
}
