<?php

use Illuminate\Support\Str;

if (!function_exists('bearerToken')) {
    function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
        return false;
    }
}

