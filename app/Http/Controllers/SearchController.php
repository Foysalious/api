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
            $query = Service::where('publication_status', 1)->whereHas('tags', function ($q) use ($request, $search_words) {
                foreach ($search_words as $word) {
                    $q->orwhere('name', 'like', "%" . $word . "%");
                }
            })->orWhere([['name', 'like', "%" . $search_text . "%"], ['publication_status', 1]]);
            //if has parent category id
            if ($request->has('p_c')) {
                $category = Category::find($request->input('p_c'));
                $children_categories = $category->children()->pluck('id');
                $query = $query->whereIn('category_id', $children_categories);
            }
            $services = $query->select('id', 'name', 'thumb', 'banner', 'variables', 'variable_type', 'min_quantity')
                ->take(10)
                ->get();
            if ($services->isEmpty())
                return response()->json(['msg' => 'nothing found', 'code' => 404]);
            else {
                foreach ($services as $service) {
                    array_add($service, 'slug_service', str_slug($service->name));
                    //if service has no partners
                    if ($service->partners->isEmpty()) {
                        array_add($service, 'review', 0);
                        array_add($service, 'rating', 0);
                        array_add($service, 'start_price', 0);
                        array_add($service, 'end_price', 0);
                        continue;
                    }
                    $service = $this->serviceRepository->getStartPrice($service, $request->location);
                    // review count of this partner for this service
                    $review = $service->reviews()->where('review', '<>', '')->count('review');
                    //avg rating of the partner for this service
                    $rating = $service->reviews()->avg('rating');
                    array_add($service, 'review', $review);
                    array_add($service, 'rating', $rating);
                    array_forget($service, 'partners');
                }
            }
            return response()->json(['msg' => 'successful', 'code' => 200, 'services' => $services]);
        } else
            return response()->json(['msg' => 'nothing found', 'code' => 404]);

    }

    public function searchBusinessOrMember($member, Request $request)
    {
        $search = trim($request->search);
        if ($request->searchBy == 'business') {
            $profile = $this->getProfile('email', $search, $request->business);
            if (count($profile) == 0) {
                $profile = $this->getProfile('mobile', $this->formatMobile($search), $request->business);
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
                $business = $this->getBusiness('phone', $this->formatMobile($search), $member);
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


    private function formatMobile($mobile)
    {
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $mobile)) {
            return $mobile;
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $mobile)) {
            return preg_replace('/^88/', '+88', $mobile);
        } // real mobile no add '+880' at the start
        else {
            return '+88' . $mobile;
        }
    }
}
