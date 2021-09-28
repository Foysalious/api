<?php

namespace App\Http\Controllers\UserMigration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Sheba\UserMigration\UserMigrationService;
use App\Sheba\UserMigration\UserMigrationStrategy;

class UserMigrationController extends Controller
{
    private $modules;
    private $userMigrationSvc;

    public function __construct(UserMigrationService $migrationService)
    {
        $this->modules = config('user_migration.modules');
        $this->userMigrationSvc = $migrationService;
    }

    public function getMigrationList(Request $request)
    {
        $banner = null;
        $modules = $this->modules;
        foreach ($modules as $key => $value) {
            /** @var UserMigrationStrategy $class */
            $class = $this->userMigrationSvc->resolveClass($value['key']);
            $modules[$key]['status'] = $class->getStatus();
            if ($value['priority'] == 1) {
                $banner = $class->getBanner();
            }
        }
        $res['modules'] = $modules;
        $res['banner'] = $banner;
        return api_response($request, $res, 200, ['data' => $res]);
    }

    public function updateMigration($moduleKey)
    {

    }
}