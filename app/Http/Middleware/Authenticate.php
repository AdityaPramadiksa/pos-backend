<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Tentukan kemana user diarahkan jika tidak terautentikasi.
     *
     */
    protected function redirectTo(Request $request): ?string
{
    if ($request->expectsJson() || $request->is('api/*')) {
        return null;
    }

    // Ubah dari admin.login menjadi login
    return route('login');
}
}
