<?php

namespace App\Http\Controllers\UserMigration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use Sheba\UserMigration\UserMigrationService;
use Sheba\UserMigration\UserMigrationStrategy;

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
        $modules = [
            'modules' => $this->modules
        ];
        $banner = null;
        foreach ($modules as $module) {
            /** @var UserMigrationStrategy $class */
            $class = $this->userMigrationSvc->resolveClass($module['key']);
            $module['status'] = $class->getStatus();
            if ($module['priority'] == 1) {
                $banner = $class->getBanner();
            }
        }
        $modules['banner'] = $banner;
        return api_response($request, $modules, 200, ['data' => $modules]);
    }
}