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

    public function create(Request $request)
    {
        $this->validate($request, [
            'status' => 'required',
            'user_id' => 'required',
            'user_type' => 'required'
        ]);
        if (!in_array($request->status, UserStatus::get())) {
            throw new Exception('Invalid Status', 404);
        }
        if ($request->partner->id != $request->user_id) {
            throw new Exception('You are not an admin of this partner', 401);
        }
        $data = [
          'status'      => $request->status,
          'user_id'     => $request->user_id,
          'user_type'   => strtolower($request->user_type),
        ];
        $user = $this->userMigrationRepo->create($data);
        return api_response($request, $user, 200, ['data' => $user]);
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
            return api_response($request, null, $e->getCode() == 0 ? 400 : $e->getCode(), ['message' => $e->getMessage()]);
        }
    }
}