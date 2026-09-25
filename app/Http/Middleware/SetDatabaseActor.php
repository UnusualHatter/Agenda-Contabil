<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

// Tells the audit triggers which team member is behind the queries.
final class SetDatabaseActor
{
    public function handle(Request $request, Closure $next): Response
    {
        DB::statement("SELECT set_config('app.user_id', ?, false)", [(string) $request->user()?->id]);

        return $next($request);
    }
}
