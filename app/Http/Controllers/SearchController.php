<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Member;
use App\Models\Profile;
use App\Models\Service;
use App\Repositories\InvitationRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    private $serviceRepository;
    private $inviteRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->inviteRepository = new InvitationRepository();
    }

    public function searchService(Request $request)
    {
        if ($request->s != '') {
            $search_text = trim($request->s);
            $search_words = explode(' ', $search_text);
            $query = Service::query();
            $query = $query->where('publication_status', 1)->whereHas('tags', function ($q) use ($request, $search_words) {
                foreach ($search_words as $word) {
                    $q->orwhere('name', 'like', "%" . $word . "%");
                }
            })->orWhere([['name', 'like', "%" . $search_text . "%"], ['publication_status', 1]]);
            //if has parent category id
            $services = $query->select('id', 'name', 'thumb', 'banner', 'variables', 'variable_type', 'min_quantity', 'category_id')
                ->take(10)
                ->get();
            if ($request->has('p_c')) {
                $category = Category::find($request->p_c);
                $children_categories = $category->children()->pluck('id');
                $services = $services->whereIn('category_id', $children_categories->values()->all());
            }
            if (count($services) == 0)
                return api_response($request, null, 404);
            else {
                $services = $this->serviceRepository->getpartnerServicePartnerDiscount($services);
                $services = $this->serviceRepository->addServiceInfo($services, ['start_price', 'reviews']);
            }
            return api_response($request, null, 200, ['services' => $services]);
        } else
            return api_response($request, null, 404);

    }

    public function search(Request $request)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required',
                'type' => 'required|in:customer,resource',
            ]);
            $mobile = formatMobile($request->mobile);
            $type = $request->type;
            if ($profile = Profile::where('mobile', $mobile)->first()) {
                if ($avatar = $profile->$type) {
                    $user = array(
                        'id' => $avatar->id,
                        'name' => $avatar->profile->name,
                        'mobile' => $avatar->profile->mobile,
                        'email' => $avatar->profile->email,
                        'address' => $avatar->profile->address,
                        'remember_token' => $avatar->remember_token
                    );
                    return api_response($request, $user, 200, ["user" => $user]);
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function searchBusinessOrMember($member, Request $request)
    {
        $search = trim($request->search);
        if ($request->searchBy == 'business') {
            $profile = $this->getProfile('email', $search, $request->business);
            if (count($profile) == 0) {
                $profile = $this->getProfile('mobile', formatMobile($search), $request->business);
            }
            if (count($profile) != 0) {
                if ($profile->member != null) {
                    if ($profile->member->id == $member) {
                        return response()->json(['msg' => "seriously??? can't send invitation to yourself", 'code' => 500]);
                    } elseif (count($profile->member->businesses) > 0) {
                        return response()->json(['msg' => "already a member!", 'code' => 409]);
                    }
                }
                array_forget($profile, 'member');
                if (!$this->inviteRepository->alreadySent($profile->id, $request->business, 'business'))
                    return response()->json(['result' => $profile, 'code' => 200]);
                else
                    return response()->json(['msg' => 'Member already sent you request!', 'code' => 409]);
            } else {
                return response()->json(['msg' => 'search person not found', 'code' => 404]);
            }
        } elseif ($request->searchBy == 'member') {
            $business = $this->getBusiness('email', $search, $member);
            if (count($business) == 0) {
                $business = $this->getBusiness('phone', formatMobile($search), $member);
            }
            if (count($business->members) != 0) {
                return response()->json(['msg' => 'already a member', 'code' => 409]);
            }
            if ($business != null) {
                $member = Member::find($member);
                if (!$this->inviteRepository->alreadySent($member->profile_id, $business->id, 'member'))
                    return response()->json(['msg' => 'found', 'code' => 200, 'result' => $business]);
                else
                    return response()->json(['msg' => 'business already sent you request!', 'code' => 409]);
            } else {
                return response()->json(['msg' => 'business not found!', 'code' => 409]);
            }
        }
    }

    private function getProfile($field, $search, $business)
    {
        return Profile::with(['member' => function ($q) use ($business) {
            $q->select('id', 'profile_id')->with(['businesses' => function ($q) use ($business) {
                $q->select('businesses.id')->where('businesses.id', $business);
            }]);
        }])->with(['joinRequests' => function ($q) use ($business) {
            $q->select('id', 'profile_id', 'status')->where([
                ['requester_type', "App\Models\Business"],
                ['organization_id', $business]
            ]);
        }])->select('id', 'name', 'pro_pic')->where($field, $search)->first();

    }

    private function getBusiness($field, $search, $member)
    {
        return Business::with(['joinRequests' => function ($q) {
            $q->select('*')->where('requester_type', 'App\Models\Profile');
        }])->with(['members' => function ($q) use ($member) {
            $q->select('members.id')->where('members.id', $member);
        }])->where($field, $search)->select('id', 'name', 'logo', 'email', 'phone')->first();
    }

}
