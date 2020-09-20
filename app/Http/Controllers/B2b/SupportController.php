<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Sheba\Dal\Support\Model as Support;
use Illuminate\Http\Request;
use Sheba\Business\Support\Updater;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class SupportController extends Controller
{
    /** @var SupportRepositoryInterface */
    private $repo;

    public function __construct(SupportRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function resolve($member, $support, Updater $updater, Request $request)
    {
        try {
            $support = $this->repo->find($support);
            if (!$support) return api_response($request, null, 404);
            $business_member = $request->business_member;
            $support = $updater->setSupport($support)->setBusinessMember($business_member)->resolve();
            if (!$support) return api_response($request, null, 500);
            return api_response($request, $support, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function index($business, Request $request, BusinessMemberRepositoryInterface $business_member_repository)
    {
        try {
            $members = $business_member_repository->where('business_id', $business)->select('id', 'member_id')->get()->pluck('member_id')->toArray();
            list($offset, $limit) = calculatePagination($request);
            $supports = Support::whereIn('member_id', $members)
                ->select('id', 'member_id', 'status', 'long_description', 'created_at', 'closed_at', 'is_satisfied');

            if ($request->has('status')) $supports = $supports->where('status', $request->status);

            if ($request->has('start_date') && $request->has('end_date')) {
                $time_frame = (new TimeFrame())->forTwoDates($request->start_date, $request->end_date);
                $supports = $supports->whereBetween('created_at', $time_frame->getArray());
            }
            $supports_without_limit = clone $supports;

            if ($request->has('limit')) $supports = $supports->skip($offset)->limit($limit);
            $supports = $supports->orderBy('id', 'desc')->get();
            if (count($supports) == 0) return api_response($request, null, 404);

            $supports->map(function (&$support) {
                $support['date'] = $support->created_at->format('M d');
                $support['time'] = $support->created_at->format('h:i A');
                return $support;
            });
            return api_response($request, $supports, 200, [
                'supports' => $supports,
                'filtered_supports' => $supports_without_limit->count(),
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $business, $support)
    {
        try {
            $support = $this->repo->find($support);
            if (!$support) return api_response($request, null, 404);
            $support['date'] = $support->created_at->format('M d');
            $support['time'] = $support->created_at->format('h:i A');
            $support['requested_by'] = [
                'name' => $support->member->profile->name,
                'image' => $support->member->profile->pro_pic,
                'designation' => $support->member->businessMember->role ? $support->member->businessMember->role->name : ''
            ];
            removeRelationsAndFields($support);
            return api_response($request, $support, 200, ['support' => $support]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
