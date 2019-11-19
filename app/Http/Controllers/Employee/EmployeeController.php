<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class EmployeeController extends Controller
{

    public function getDashboard(Request $request, MemberRepositoryInterface $member_repository)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 404);
            $member = $member_repository->find($business_member['member_id']);
            if ($business_member) return api_response($request, $business_member, 200, ['info' => [
                'notification_count' => $member->notifications()->unSeen()->count()
            ]]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}