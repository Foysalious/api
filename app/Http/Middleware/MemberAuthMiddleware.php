<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;

class MemberAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        If ($request->has('remember_token') && !empty($request->remember_token)) {
            $member = Member::where('remember_token', $request->remember_token)->first();
            if (count($member) != 0) {
                if ($member->id == $request->member) {
                    return $next($request);
                }
            }
        }
        return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }
}
