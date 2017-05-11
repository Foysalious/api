<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Profile;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getService(Request $request)
    {
        if ($request->input('s') != '') {
            $query = Service::where('name', 'like', "%" . $request->input('s') . "%");
            //if has parent category id
            if ($request->has('p_c')) {
                $category = Category::find($request->input('p_c'));
                $children_categories = $category->children()->pluck('id');
                $query = $query->whereIn('category_id', $children_categories);
            }
            $services = $query->where('publication_status', 1)
                ->select('id', 'name', 'thumb', 'banner', 'variables', 'variable_type')
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
//                    $service = $this->serviceRepository->getStartEndPrice($service);
                    // review count of this partner for this service
                    $review = $service->reviews()->where('review', '<>', '')->count('review');
                    //avg rating of the partner for this service
                    $rating = $service->reviews()->avg('rating');
                    array_add($service, 'review', $review);
                    array_add($service, 'rating', $rating);
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
                    }
                }
                array_forget($profile, 'member');
                return response()->json(['result' => $profile, 'code' => 200]);
            } else {
                return response()->json(['msg' => 'search person not found', 'code' => 404]);
            }
        } elseif ($request->searchBy == 'member') {
            $business = $this->getBusiness('email', $search);
            if (count($business) == 0) {
                $business = $this->getBusiness('phone', $this->formatMobile($search));
            }
            if ($business != null) {
                return response()->json(['msg' => 'found', 'code' => 200, 'result' => $business]);
            } else {
                return response()->json(['msg' => 'business not found!', 'code' => 409]);
            }
        }
    }

    private function getProfile($field, $search, $business)
    {
        return Profile::with(['member' => function ($q) {
            $q->select('id', 'profile_id');
        }])->with(['joinRequests' => function ($q) use ($business) {
            $q->select('id', 'profile_id', 'status')->where([
                ['requester_type', "App\Models\Business"],
                ['organization_id', $business]
            ]);
        }])->select('id', 'name', 'pro_pic')->where($field, $search)->first();

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

    private function getBusiness($field, $search)
    {
        return Business::with(['joinRequests' => function ($q) {
            $q->select('*');
        }])->where($field, $search)->select('id', 'name', 'logo')->first();
    }
}
