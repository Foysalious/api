<?php namespace App\Http\Middleware;


class JwtAccessTokenMiddleware extends AccessTokenMiddleware
{
    protected function formApiResponse($request, $internal, $code, array $data)
    {
        return http_response($request, $internal, $code, $data);
    }

}