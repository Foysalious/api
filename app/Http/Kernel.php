<?php namespace App\Http;

use App\Http\Middleware\AffiliateAuthMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\B2B\OrderMiddleware;
use App\Http\Middleware\BusinessManagerAuthMiddleware;
use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\Cors2MiddleWare;
use App\Http\Middleware\CustomerAuthMiddleware;
use App\Http\Middleware\CustomerJobAuthMiddleware;
use App\Http\Middleware\DLSApiVersioning;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ExternalPaymentLinkAuthMiddleware;
use App\Http\Middleware\GeoAuthMiddleware;
use App\Http\Middleware\JWT\ResourceAuthMiddleware;
use App\Http\Middleware\JWTAuthentication;
use App\Http\Middleware\JWTAuthMiddleware;
use App\Http\Middleware\ManagerAuthMiddleware;
use App\Http\Middleware\MemberAuthMiddleware;
use App\Http\Middleware\PartnerJobAuthMiddleware;
use App\Http\Middleware\PartnerOrderAuthMiddleware;
use App\Http\Middleware\PartnerResourceAuthMiddleware;
use App\Http\Middleware\PaymentLinkAuthMiddleware;
use App\Http\Middleware\ProfileAuthMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\ResourceJobAuthMiddleware;
use App\Http\Middleware\Sheba\ShebaNetworkMiddleware;
use App\Http\Middleware\ThrottleRequests;
use App\Http\Middleware\TopUpAuthMiddleware;
use App\Http\Middleware\VendorMiddleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Fideloper\Proxy\TrustProxies;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\Authorize;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
        Middleware\CheckForMaintenanceMode::class,
        TrustProxies::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
        ],
        'api' => [
            'throttle:60,1',
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
        'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
        'cors2' => Cors2MiddleWare::class,
        'sheba_network' => ShebaNetworkMiddleware::class,
        'customer.auth' => CustomerAuthMiddleware::class,
        'customer_job.auth' => CustomerJobAuthMiddleware::class,
        'profile.auth' => ProfileAuthMiddleware::class,
        'affiliate.auth' => AffiliateAuthMiddleware::class,
        'member.auth' => MemberAuthMiddleware::class,
        'resource.auth' => Middleware\ResourceAuthMiddleware::class,
        'resource.jwt.auth' => ResourceAuthMiddleware::class,
        'manager.auth' => ManagerAuthMiddleware::class,
        'business.auth' => BusinessManagerAuthMiddleware::class,
        'partner_job.auth' => PartnerJobAuthMiddleware::class,
        'partner_order.auth' => PartnerOrderAuthMiddleware::class,
        'partner_resource.auth' => PartnerResourceAuthMiddleware::class,
        'resource_job.auth' => ResourceJobAuthMiddleware::class,
        'vendor.auth' => VendorMiddleware::class,
        'jwtAuth' => JWTAuthentication::class,
        'jwtGlobalAuth' => JWTAuthMiddleware::class,
        'business_order.auth' => OrderMiddleware::class,
        'topUp.auth' => TopUpAuthMiddleware::class,
        'paymentLink.auth' => PaymentLinkAuthMiddleware::class,
        'geo.auth' => GeoAuthMiddleware::class,
        'loan.version' => DLSApiVersioning::class,
        'external_payment_link.auth' => ExternalPaymentLinkAuthMiddleware::class
    ];
}
