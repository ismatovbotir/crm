<?php

namespace App\Http\Middleware;

use App\Helpers\Acl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Защита внутреннего контура (CRM /admin/*).
 *
 * Пропускает только сотрудников RSG (internal scope).
 * Клиенты получают 403 (или редирект на /portal).
 */
class EnsureUserIsInternal
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Acl::isInternal()) {
            // Если пользователь — клиент, отправляем в портал
            if (Acl::isClient()) {
                return redirect('/portal');
            }
            abort(403, 'Доступ только для сотрудников RSG');
        }

        return $next($request);
    }
}
