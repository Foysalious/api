<?php

namespace App\Http\Controllers\UserMigration;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use App\Sheba\UserMigration\Modules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Sheba\UserMigration\UserMigrationService;
use App\Sheba\UserMigration\UserMigrationRepository;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;

class UserMigrationController extends Controller
{
    const X_API_KEY = 'sheba_user_migration';

    private $modules;
    private $userMigrationSvc;

    public function __construct(UserMigrationService $migrationService)
    {
        $this->modules          = config('user_migration.modules');
        $this->userMigrationSvc = $migrationService;
    }

    /**
     * @throws Exception
     */
    public function getMigrationList(Request $request): JsonResponse
    {
        $banner  = null;
        $modules = $this->modules;
        $userId  = $request->partner->id;
        foreach ($modules as $key => $value) {
            /** @var UserMigrationRepository $class */
            $class                   = $this->userMigrationSvc->resolveClass($value['key']);
            $modules[$key]['status'] = $class->setUserId($userId)->setModuleName($value['key'])->getStatus();
            if ($value['priority'] == 1) {
                $banner = $class->getBanner();
            }
        }
        $res['modules'] = $modules;
        $res['banner']  = $banner;
        return api_response($request, $res, 200, ['data' => $res]);

    }

    /**
     * @throws Exception
     */
    public function migrationStatusByModuleName(Request $request, $moduleName): JsonResponse
    {
        $userId = $request->partner->id;
        /** @var UserMigrationRepository $class */
        $class = $this->userMigrationSvc->resolveClass($moduleName);
        $res   = $class->setUserId($userId)->setModuleName($moduleName)->getStatusWiseResponse();
        return api_response($request, $res, 200, ['data' => $res]);
    }

    /**
     * @throws Exception
     */
    public function updateMigrationStatus(Request $request, $moduleName, $partner): JsonResponse
    {
        $this->validate($request, ['status' => 'required|string']);
        if (!empty($partner)) {
            $moduleName = $partner;
            $partner    = Partner::find($moduleName);
            $request->merge(['partner' => $partner, 'user' => User::find(1)]);
        }
        $userId = $request->partner->id;
        if (!in_array($request->status, UserStatus::get())) throw new Exception('Invalid Status');
        if (!in_array($moduleName, Modules::get())) throw new Exception('Invalid Module');
        /** @var UserMigrationRepository $class */
        $class = $this->userMigrationSvc->resolveClass($moduleName);
        $res   = $class->setUserId($userId)->setModuleName($moduleName)->setModifierUser($request->user)->updateStatus($request->status);
        return api_response($request, $res, 200, ['data' => $res]);
    }

    /**
     * @throws Exception
     */
    public function updateStatusWebHook(Request $request): JsonResponse
    {
        if (!$request->hasHeader('X-API-KEY') || $request->header('X-API-KEY') != self::X_API_KEY) {
            throw new Exception('Invalid Request!', 400);
        }
        $this->validate($request, ['status' => 'required|string', 'module_name' => 'required|string', 'user_id' => 'required']);
        /** @var UserMigrationRepository $class */
        $class = $this->userMigrationSvc->resolveClass($request->module_name);
        $res   = $class->setUserId($request->user_id)->setModuleName($request->module_name)->setModifierUser(User::find(1))->updateStatus($request->status);
        return api_response($request, $res, 200, ['data' => $res]);
    }

    /**
     * @throws Exception
     */
    public function checkModuleAccess(Request $request, $moduleName)
    {
        if (!$request->hasHeader('version-code')) {
            throw new Exception('Invalid Request!', 400);
        }
        foreach ($this->modules as $key => $value) {
            if ($value['key'] == $moduleName) {
                /** @var UserMigrationRepository $class */
                $class = $this->userMigrationSvc->resolveClass($moduleName);
                $res   = $class->versionCodeCheck($request->header('version-code'), $value);
                return api_response($request, $res, 200, ['data' => $res]);
            }
        }
        throw new Exception('Module Not Found!', 400);
    }
}