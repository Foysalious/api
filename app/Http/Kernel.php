<?php

namespace App\Http;

use App\Http\Middleware\B2B\OrderMiddleware;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\GeoAuthMiddleware;
use App\Http\Middleware\PaymentLinkAuthMiddleware;
use App\Http\Middleware\TopUpAuthMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        Middleware\CheckForMaintenanceMode::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \App\Http\Middleware\ThrottleRequests::class,
        'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
        'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
        'cors2' => \App\Http\Middleware\Cors2MiddleWare::class,
        'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
        'customer.auth' => \App\Http\Middleware\CustomerAuthMiddleware::class,
        'customer_job.auth' => \App\Http\Middleware\CustomerJobAuthMiddleware::class,
        'profile.auth' => \App\Http\Middleware\ProfileAuthMiddleware::class,
        'affiliate.auth' => \App\Http\Middleware\AffiliateAuthMiddleware::class,
        'member.auth' => \App\Http\Middleware\MemberAuthMiddleware::class,
        'resource.auth' => \App\Http\Middleware\ResourceAuthMiddleware::class,
        'manager.auth' => \App\Http\Middleware\ManagerAuthMiddleware::class,
        'business.auth' => \App\Http\Middleware\BusinessManagerAuthMiddleware::class,
        'partner_job.auth' => \App\Http\Middleware\PartnerJobAuthMiddleware::class,
        'partner_order.auth' => \App\Http\Middleware\PartnerOrderAuthMiddleware::class,
        'partner_resource.auth' => \App\Http\Middleware\PartnerResourceAuthMiddleware::class,
        'resource_job.auth' => \App\Http\Middleware\ResourceJobAuthMiddleware::class,
        'vendor.auth' => \App\Http\Middleware\VendorMiddleware::class,
        'jwtAuth' => \App\Http\Middleware\JWTAuthentication::class,
        'jwtGlobalAuth' => \App\Http\Middleware\JWTAuthMiddleware::class,
        'business_order.auth' => OrderMiddleware::class,
        'topUp.auth' => TopUpAuthMiddleware::class,
        'paymentLink.auth' => PaymentLinkAuthMiddleware::class,
        'geo.auth' => GeoAuthMiddleware::class
    ];
}
