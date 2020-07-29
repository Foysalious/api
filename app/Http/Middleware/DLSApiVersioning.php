<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Loan\LoanCompletionController;
use App\Http\Controllers\Loan\LoanCompletionV2Controller;
use App\Http\Controllers\Loan\LoanController;
use App\Http\Controllers\Loan\LoanV2Controller;
use Closure;

class DLSApiVersioning
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $version = (int)$request->header('Version-Code');
        if (isset($version) && $version > (int)config('loan.old_app_version')) {
            app()->bind(LoanController::class, LoanV2Controller::class);
            app()->bind(LoanCompletionController::class, LoanCompletionV2Controller::class);
        }
        return $next($request);
    }
}
