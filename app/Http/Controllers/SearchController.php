<?php namespace App\Http\Controllers;

use App\Models\Business;
use phpseclib3\Math\PrimeField;
use Sheba\Dal\Category\Category;
use App\Models\Member;
use App\Models\Profile;
use Sheba\Dal\Service\Service;
use App\Repositories\InvitationRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    /** @var ServiceRepository  */
    private $serviceRepository;
    /** @var InvitationRepository  */
    private $inviteRepository;

    public function __construct(ServiceRepository $service_repo, InvitationRepository $invite_repo)
    {
        $this->serviceRepository = $service_repo;
        $this->inviteRepository = $invite_repo;
    }

    public function searchService(Request $request)
    {
        if ($request->s == '') return api_response($request, null, 404);

        $search_text = trim($request->s);
        $query = Service::query()
            ->where('publication_status', 1)
            ->where(function ($q) use ($search_text) {
                $q
                    ->whereHas('tags', function ($tag_query) use ($search_text) {
                        $search_words = explode(' ', $search_text);
                        foreach ($search_words as $word) {
                            $tag_query->orwhere('name', 'like', "%" . $word . "%");
                        }
                    })
                    ->orWhere('name', 'like', "%" . $search_text . "%");
            });

        //if has parent category id
        $services = $query->select('id', 'name', 'thumb', 'banner', 'variables', 'variable_type', 'min_quantity', 'category_id')
            ->take(10)
            ->get();
        if ($request->has('p_c')) {
            $category = Category::find($request->p_c);
            $children_categories = $category->children()->pluck('id');
            $services = $services->whereIn('category_id', $children_categories->values()->all());
        }
        if (count($services) == 0) return api_response($request, null, 404);

        $services = $this->serviceRepository->getpartnerServicePartnerDiscount($services);
        $services = $this->serviceRepository->addServiceInfo($services, ['start_price', 'reviews']);
        return api_response($request, null, 200, ['services' => $services]);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required',
            'type' => 'required|in:customer,resource',
        ]);
        $mobile = formatMobile($request->mobile);
        $type = $request->type;
        $profile = Profile::where('mobile', $mobile)->first();
        $avatar = $profile ? $profile->$type : null;
        if (!$profile || !$avatar) return api_response($request, null, 404);

        $user = [
            'id' => $avatar->id,
            'name' => $avatar->profile->name,
            'mobile' => $avatar->profile->mobile,
            'email' => $avatar->profile->email,
            'address' => $avatar->profile->address,
            'remember_token' => $avatar->remember_token
        ];
        return api_response($request, $user, 200, ["user" => $user]);
    }

    public function searchBusinessOrMember($member, Request $request)
    {
        $search = trim($request->search);

        if ($request->searchBy == 'business') return $this->searchByBusiness($member, $search, $request);

        if ($request->searchBy == 'member') return $this->searchByMember($member, $search);

        return response()->json(['msg' => "Invalid request!", 'code' => 400]);
    }

    private function searchByBusiness($member, $search, $request)
    {
        $profile = $this->getProfile('email', $search, $request->business);
        $profile =  $profile ?: $this->getProfile('mobile', formatMobile($search), $request->business);
        if (!$profile) return response()->json(['msg' => 'search person not found', 'code' => 404]);

        if ($profile->member != null) {
            if ($profile->member->id == $member) {
                return response()->json(['msg' => "seriously??? can't send invitation to yourself", 'code' => 500]);
            } elseif (count($profile->member->businesses) > 0) {
                return response()->json(['msg' => "already a member!", 'code' => 409]);
            }
        }
        array_forget($profile, 'member');
        if ($this->inviteRepository->alreadySent($profile->id, $request->business, 'business'))
            return response()->json(['msg' => 'Member already sent you request!', 'code' => 409]);

        return response()->json(['result' => $profile, 'code' => 200]);
    }

    private function searchByMember($member, $search)
    {
        $business = $this->getBusiness('email', $search, $member);
        $business = $business ?: $this->getBusiness('phone', formatMobile($search), $member);

        if (count($business->members) != 0) return response()->json(['msg' => 'already a member', 'code' => 409]);
        if (!$business) return response()->json(['msg' => 'business not found!', 'code' => 409]);

        $member = Member::find($member);
        if ($this->inviteRepository->alreadySent($member->profile_id, $business->id, 'member'))
            return response()->json(['msg' => 'business already sent you request!', 'code' => 409]);

        return response()->json(['msg' => 'found', 'code' => 200, 'result' => $business]);
    }

    /**
     * @return Profile | null
     */
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

    /**
     * @return Business | null
     */
    private function getBusiness($field, $search, $member)
    {
        return Business::with(['joinRequests' => function ($q) {
            $q->select('*')->where('requester_type', 'App\Models\Profile');
        }])->with(['members' => function ($q) use ($member) {
            $q->select('members.id')->where('members.id', $member);
        }])->where($field, $search)->select('id', 'name', 'logo', 'email', 'phone')->first();
    }
}

