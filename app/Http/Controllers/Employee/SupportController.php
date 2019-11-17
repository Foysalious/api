<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Business\Support\Creator;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class SupportController extends Controller
{

    public function store(Request $request, Creator $creator, MemberRepositoryInterface $member_repository)
    {
        try {
            $this->validate($request, [
                'description' => 'required|string',
            ]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->where('id', $business_member['member_id'])->first();
            $support = $creator->setMember($member)->setDescription($request->description)->create();
            return api_response($request, $support, 200, ['support' => ['id' => $support->id]]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function index(Request $request, SupportRepositoryInterface $support_repository)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $supports = $support_repository->where('member_id', $business_member['member_id'])->select('id', 'member_id', 'status', 'long_description')->get();
            if (count($supports) == 0) return api_response($request, null, 404);
            return api_response($request, $supports, 200, ['supports' => $supports]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}