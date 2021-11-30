<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Redis;

trait UserMigrationCheckMiddleware
{
    /**
     * checking migration is running or not
     * @param $partner
     * @return bool
     */
    public function isRouteAccessAllowed($partner)
    {
        if (!$partner) {
            return true;
        }
        $isMigrationRunning = Redis::get("user-migration:".$partner->id);
        if ($isMigrationRunning) {
            $routes = config('user_migration_whitelist.routes');
            return in_array(request()->route()->getName(), $routes);
        }
        return true;
    }
}