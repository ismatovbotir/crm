<?php

namespace App\Http\Middleware;

use App\Helpers\Acl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Защита внешнего контура (Portal /portal/*).
 *
 * Пропускает только клиентов (client-admin / client-user).
 */
class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Acl::isClient()) {
            if (Acl::isInternal()) {
                return redirect('/admin');
            }
            abort(403, 'Доступ только для клиентов');
        }

        return $next($request);
    }
}
