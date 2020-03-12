<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Cors2MiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domains = [
            "http://localhost",
            "http://localhost:4200",
            "http://localhost:8080",
            "http://localhost:8081",
            "http://localhost:8082",
            "http://localhost:8083",
            "http://localhost:8084",
            "http://localhost:7891",
            "http://localhost:3333",
            "http://localhost:3334",
            "http://localhost:3335",
            "http://localhost:3000",
            "http://103.26.139.148",
            "http://144.76.92.216",
            "http://sheba.test",
            "http://ticket.sheba.test",
            "https://developer.sslcommerz.com",
            "https://www.sslcommerz.com",
            "http://dev-sheba.xyz",
            "http://business.dev-sheba.xyz",
            "http://www.dev-sheba.xyz",
            "https://www.dev-sheba.xyz",
            "http://admin.dev-sheba.xyz",
            "https://admin.dev-sheba.xyz",
            "http://partners.dev-sheba.xyz",
            "https://partners.dev-sheba.xyz",
            "http://accounts.dev-sheba.xyz",
            "http://transport.dev-sheba.xyz",
            "http://movie.dev-sheba.xyz",
            "http://api.sheba.test",
            "https://api.dev-sheba.xyz",
            "http://sheba.dev",
            "http://bondhu.dev-sheba.xyz",
            "https://bondhu.dev-sheba.xyz",
            "https://topup.dev-sheba.xyz",
            null,
            "null",
            "chrome-extension://fhbjgbiflinjbdggehcddcbncdddomop",
            "file://",
            "http://admin.sheba.test",
            "http://partners.sheba.test",
            "http://loan.sheba.test",
            "http://partners.sheba.new",
            "https://www.sheba.xyz",
            "https://admin.sheba.xyz",
            "https://partners.sheba.xyz",
            "http://admin.sheba.new",
            "http://accounts.sheba.test",
            "https://sandbox.sslcommerz.com",
            "https://securepay.sslcommerz.com",
            "https://sandbox.thecitybank.com:4443",
            "https://epay.thecitybank.com:7788",
            "https://epay.thecitybank.com:443",
            "https://epay.thecitybank.com",
            "http://bondhu.sheba.xyz",
            "https://bondhu.sheba.xyz",
            "https://api.sheba.xyz",
            "http://0.0.0.0:3333",
            "http://b2b.sheba.test",
            "https://b2b.dev-sheba.xyz",
            "https://business.sheba.xyz",
            "https://pl.dev-sheba.xyz",
            "https://pl.sheba.xyz",
            "https://topup.sheba.xyz",
            "https://transport.sheba.xyz",
            "https://movie.sheba.xyz",
            "http://www.new.dev-sheba.xyz",
            "https://www.new.dev-sheba.xyz",
            "https://new.dev-sheba.xyz",
            "https://banks.dev-sheba.xyz",
            "https://banks.sheba.xyz",
            "http://stage.sheba.xyz",
            "https://stage.sheba.xyz",
            "https://admin.stage.sheba.xyz",
            "http://103.97.44.39"
        ];
        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Credentials'] = 'true';
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With, Portal-Name, User-Id';
        $headers['Access-Control-Allow-Origin'] = '*';
        if (!in_array($request->server('HTTP_ORIGIN'), $domains)) {
            return response()->json(['message' => 'Unauthorized domain :'.$request->server('HTTP_ORIGIN'), 'code' => 401])->withHeaders($headers);
        }

        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With, Portal-Name, User-Id';
        $response = $next($request);
        foreach ($headers as $key => $value) {
            if ($response instanceof BinaryFileResponse){}
            else $response->header($key, $value);
        }
        return $response;
    }
}
