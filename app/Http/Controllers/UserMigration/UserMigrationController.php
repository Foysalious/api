<?php

namespace App\Http\Controllers\UserMigration;

use App\Http\Controllers\Controller;
use App\Sheba\UserMigration\Modules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Sheba\UserMigration\UserMigrationService;
use App\Sheba\UserMigration\UserMigrationRepository;
use Exception;
use Sheba\Dal\UserMigration\UserStatus;

class UserMigrationController extends Controller
{
    CONST X_API_KEY = 'sheba_user_migration';

    private $modules;
    private $userMigrationSvc;

    public function __construct(UserMigrationService $migrationService)
    {
        $this->modules = config('user_migration.modules');
        $this->userMigrationSvc = $migrationService;
    }

    public function getMigrationList(Request $request): JsonResponse
    {
        try {
            $banner = null;
            $modules = $this->modules;
            $userId = $request->partner->id;
            foreach ($modules as $key => $value) {
                /** @var UserMigrationRepository $class */
                $class = $this->userMigrationSvc->resolveClass($value['key']);
                $modules[$key]['status'] = $class->setUserId($userId)->setModuleName($value['key'])->getStatus();
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

    public function migrationStatusByModuleName(Request $request, $moduleName): JsonResponse
    {
        try {
            $userId = $request->partner->id;
            /** @var UserMigrationRepository $class */
            $class = $this->userMigrationSvc->resolveClass($moduleName);
            $res = $class->setUserId($userId)->setModuleName($moduleName)->getStatusWiseResponse();
            return api_response($request, $res, 200, ['data' => $res]);
        } catch (Exception $e) {
            return api_response($request, null, 404, ['message' => $e->getMessage(), 'code' => 404]);
        }
    }

    public function updateMigrationStatus(Request $request, $moduleName): JsonResponse
    {
        try {
            $this->validate($request, ['status' => 'required|string']);
            $userId = $request->partner->id;
            if (!in_array($request->status, UserStatus::get())) throw new Exception('Invalid Status');
            if (!in_array($moduleName, Modules::get())) throw new Exception('Invalid Module');
            /** @var UserMigrationRepository $class */
            $class = $this->userMigrationSvc->resolveClass($moduleName);
            $res = $class->setUserId($userId)->setModuleName($moduleName)->updateStatus($request->status);
            return api_response($request, $res, 200, ['data' => $res]);
        } catch (Exception $e) {
            dd($e);
            return api_response($request, null, 400, ['message' => $e->getMessage(), 'code' => 400]);
        }
    }

    public function updateStatusWebHook(Request $request): JsonResponse
    {
        try {
            if(!$request->hasHeader('X-API-KEY') || $request->header('X-API-KEY') != self::X_API_KEY) {
                throw new Exception('Invalid Request!', 400);
            }
            $this->validate($request, ['status' => 'required|string', 'module_name' => 'required|string', 'user_id' => 'required']);
            /** @var UserMigrationRepository $class */
            $class = $this->userMigrationSvc->resolveClass($request->module_name);
            $res = $class->setUserId($request->user_id)->setModuleName($request->module_name)->updateStatus($request->status);
            return api_response($request, $res, 200, ['data' => $res]);
        } catch (Exception $e) {
            return api_response($request, null, 404, ['message' => $e->getMessage(), 'code' => 404]);
        }
    }

    public function canAccessModule(Request $request, $moduleKey)
    {
        try {
            if(!$request->hasHeader('version-code')) {
                throw new Exception('Invalid Request!', 400);
            }
            foreach ($this->modules as $key => $value) {
                if ($value['key'] == $moduleKey) {
                    if ((int) $request->header('version-code') >= $value['app_version']) {
                        return [
                            'code' => 200,
                            'message' => 'You are allowed to use.'
                        ];

                    } else {
                        return [
                            'code' => 403,
                            'message' => 'Your application is outdated! Please update.'
                        ];
                    }
                }
            }
            throw new Exception('Module Not Found!', 400);
        } catch (Exception $e) {
            return api_response($request, null, 404, ['message' => $e->getMessage(), 'code' => 404]);
        }
    }
}