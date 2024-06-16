<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // Получаем текущего пользователя

        // dd($user);
        if ($user && $user->role_id == Role::ROLE_TEACHER) {
            return $next($request);
        }

        abort(403, 'Доступ запрещен');
    }
}
