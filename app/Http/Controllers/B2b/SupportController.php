<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Business\Support\Updater;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class SupportController extends Controller
{

    public function resolve($member, $support, SupportRepositoryInterface $support_repository, BusinessMemberRepositoryInterface $business_member_repository,
                            Updater $updater, Request $request)
    {
        try {
            $support = $support_repository->where('id', $support)->first();
            if (!$support) return api_response($request, null, 404);
            $business_member = $request->business_member;
            $support = $updater->setSupport($support)->setBusinessMember($business_member)->resolve();
            if (!$support) return api_response($request, null, 500);
            return api_response($request, $support, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, $support, 500);
        }
    }
}