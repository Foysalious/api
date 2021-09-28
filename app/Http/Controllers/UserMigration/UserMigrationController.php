<?php

namespace App\Http\Controllers\UserMigration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Sheba\UserMigration\UserMigrationService;
use App\Sheba\UserMigration\UserMigrationRepository;
use Exception;

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
        try{
            $banner = null;
            $modules = $this->modules;
            $userId = $request->partner->id;
            foreach ($modules as $key => $value) {
                /** @var UserMigrationRepository $class */
                $class = $this->userMigrationSvc->resolveClass($value['key']);
                $modules[$key]['status'] = $class->setUserId($userId)->setModuleKey($value['key'])->getStatus();
                if ($value['priority'] == 1) {
                    $banner = $class->getBanner();
                }
            }
            $res['modules'] = $modules;
            $res['banner'] = $banner;
            return api_response($request, $res, 200, ['data' => $res]);
        } catch (Exception $e) {
            return api_response($request, null, 404, ['message' => $e->getMessage(), 'code' => 404]);
        }
    }

    public function migrationStatusByModuleKey(Request $request, $moduleKey)
    {
        try {
            /** @var UserMigrationRepository $class */
            $class = $this->userMigrationSvc->resolveClass($moduleKey);

        } catch (Exception $e) {
            return api_response($request, null, 404, ['message' => $e->getMessage(), 'code' => 404]);
        }
    }

    public function updateMigration($moduleKey)
    {

    }
}