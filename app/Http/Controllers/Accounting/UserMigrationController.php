<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Repository\UserMigrationRepository;
use Exception;
use Sheba\Dal\src\AccountingMigratedUser\UserStatus;

class UserMigrationController extends Controller
{
    /** @var UserMigrationRepository  */
    private $userMigrationRepo;

    public function __construct(UserMigrationRepository $userMigrationRepo)
    {
        $this->userMigrationRepo = $userMigrationRepo;
    }

    public function show(Request $request, $userId)
    {
        $user = $this->userMigrationRepo->show($userId);
        return api_response($request, $user, 200, ['data' => $user]);
    }

    public function update($userId, Request $request)
    {
        try {
            $this->validate($request, ['status' => 'required']);

            if (!in_array($request->status, UserStatus::get())) {
                throw new Exception('Invalid Status', 404);
            }
            $data = ['status' => $request->status];
            $user = $this->userMigrationRepo->update($data, $userId);
            return api_response($request, $user, 200, ['data' => $user]);

        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }
}