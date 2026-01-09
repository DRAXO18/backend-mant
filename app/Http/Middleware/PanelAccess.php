<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PanelAccess
{
    public function handle(Request $request, Closure $next, string $panel)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $allowed = match ($panel) {
            'client'  => $user->client()->exists(),

            'company' => $user->companyUsers()->exists()
                         || $user->technicians()->exists(),

            'rubro'   => $user->rubroUser()->exists(),

            default   => false,
        };

        if (!$allowed) {
            abort(403, 'No tienes acceso a este panel');
        }

        return $next($request);
    }
}
