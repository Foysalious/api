<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Repositories\ProfileRepository;
use Closure;
use ErrorException;

class ProfileAuthMiddleware
{
    private $profileRepo;

    public function __construct()
    {
        $this->profileRepo = new ProfileRepository();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('remember_token')) {
            if ($request->has('from')) {
                try {
                    $from = $this->profileRepo->getAvatar($request->from);
                    $avatar = null;
                    if ($from == 'customer') {
                        $avatar = Customer::where('remember_token', $request->input('remember_token'))->first();
                    } elseif ($from == 'affiliate') {
                        $avatar = Affiliate::where('remember_token', $request->input('remember_token'))->first();
                    }
                    if ($avatar != null) {
                        if ($avatar->id == $request->id) {
                            $request->merge(['profile' => $avatar->profile]);
                            return $next($request);
                        } else {
                            return response()->json(['msg' => 'unauthorized', 'code' => 409]);
                        }
                    }
                } catch (ErrorException $e) {
                    return response()->json(['msg' => 'unauthorized', 'code' => 409]);
                }
            }
        }
        return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }
}
