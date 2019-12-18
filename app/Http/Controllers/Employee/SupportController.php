<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Business\Support\Creator;
use Sheba\Business\Support\Updater;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class SupportController extends Controller
{
    use ModificationFields;

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
            $this->setModifier($member);
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
            $this->validate($request, [
                'status' => 'string|in:open,closed',
                'limit' => 'numeric',
                'offset' => 'numeric',
            ]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            list($offset, $limit) = calculatePagination($request);
            if (!$business_member) return api_response($request, null, 401);
            $supports = $support_repository->where('member_id', $business_member['member_id'])->select('id', 'member_id', 'status', 'long_description', 'created_at')->orderBy('id', 'desc');
            if ($request->has('status')) $supports = $supports->where('status', $request->status);
            if ($request->has('limit')) $supports = $supports->skip($offset)->limit($limit);
            $supports = $supports->get();
            if (count($supports) == 0) return api_response($request, null, 404);
            $supports->map(function (&$support) {
                $support['date'] = $support->created_at->format('M d');
                $support['time'] = $support->created_at->format('h:i A');
                return $support;
            });
            return api_response($request, $supports, 200, ['supports' => $supports]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $support, SupportRepositoryInterface $support_repository)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $support = $support_repository->where('id', $support)->select('id', 'member_id', 'status', 'long_description', 'created_at', 'is_satisfied', 'closed_at')->first();
            if (!$support) return api_response($request, null, 404);
            $support['date'] = $support->created_at->format('M d');
            $support['time'] = $support->created_at->format('h:i A');
            return api_response($request, $support, 200, ['support' => $support]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function feedback(Request $request, $support, SupportRepositoryInterface $support_repository, BusinessMemberRepositoryInterface $business_member_repository, Updater $updater)
    {
        try {
            $this->validate($request, ['is_satisfied' => 'required|numeric|in:0,1',]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $support = $support_repository->where('id', $support)->first();
            if (!$support) return api_response($request, null, 404);
            $support = $updater->setSupport($support)->setSatisfaction($request->is_satisfied)->giveFeedback();
            if (!$support) return api_response($request, null, 500);
            return api_response($request, $support, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}