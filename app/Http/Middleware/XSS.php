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
        'v2/businesses/*/announcements/*',
        'v2/businesses/*/bids/*/hire',
        'v2/payments/cbl/success',
        'v2/payments/cbl/fail',
        'v2/payments/cbl/cancel',
        'v2/partners/*/pos/services',
        'v2/partners/*/pos/services/*',
        'v2/partners/*/webstore-settings',
        'v2/partners/*/pos/products/orders',
        'v2/businesses/*/departments',
        'v2/businesses/*/departments/*',
        'service',
        'service/*',
        'category/*',
        '/v1/employee/attendances/action',
        'v1/employee/leaves',
        'v1/employee/approval-requests/status',
        'v1/employee/leaves/*',
        'v1/employee/approval-requests/*',
        'v1/employee/expense',
        'v1/employee/expense/*',
        'v1/employee/supports',
        'v1/employee/supports/*',
        'v1/employee/me/basic',
        'v1/employee/me',
        'v2/businesses/*/employees/*',
        'v2/businesses/*/employees/*/basic-info',
        'v2/businesses/*/leaves/approval-requests/*',
        'v2/businesses/*/leaves/approval-requests/change-status-by-super-admin',
        'v2/businesses/*/leaves/approval-requests/status',
        'v2/businesses/*/expense/filter-month',
        'v2/businesses/*/supports',
        'v2/businesses/*/supports/*'
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

        if ($this->isMethodWhitelisted($request)) return $next($request);

        $input = $request->all();

        $turned = ['&amp;'];
        $turn_back = ['&'];
        array_walk_recursive($input, function (&$input) use (&$turned, &$turn_back) {
            $input = htmlspecialchars($input, ENT_NOQUOTES | ENT_HTML5);
            $input = str_replace($turned, $turn_back, $input);
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
    protected function inExceptArray($request): bool
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

    private function isMethodWhitelisted($request): bool
    {
        return !in_array(strtolower($request->method()), ['put', 'post', 'patch']);
    }
}
