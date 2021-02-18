<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XSS
{
    /**
     * Routes that should skip handle.
     *
     * @var array
     */
    protected $except = [
        'v2/businesses/*/announcements',
        'v2/businesses/*/bids/*/hire',
        'v2/payments/cbl/success',
        'v2/payments/cbl/fail',
        'v2/payments/cbl/cancel',
        'v2/partners/*/pos/services',
        'v2/partners/*/pos/services/*',
        'v2/partners/*/webstore-settings',
        'v2/partners/*/pos/products/orders'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->inExceptArray($request)) return $next($request);
        if (!in_array(strtolower($request->method()), ['put', 'post'])) {
            return $next($request);
        }

        $input = $request->all();
        array_walk_recursive($input, function (&$input) {
            $input = htmlspecialchars($input, ENT_NOQUOTES | ENT_HTML5);
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through.
     *
     * @param Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
