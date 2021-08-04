<?php namespace App\Http\Controllers\B2b;

use App\Helper\BangladeshiMobileValidator;
use App\Http\Requests\TimeFrameReportRequest;
use App\Jobs\Business\SendMailVerificationCodeEmail;
use App\Jobs\Business\SendRFQCreateNotificationToPartners;
use App\Models\BusinessJoinRequest;
use App\Models\Member;
use App\Models\Notification;
use App\Models\Partner;
use App\Models\Procurement;
use App\Models\Profile;
use App\Models\Resource;
use App\Sheba\BankingInfo\GeneralBanking;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use App\Transformers\Business\VendorDetailsTransformer;
use App\Transformers\Business\VendorListTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\TransactionReportData;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use DB;
use Sheba\Notification\Partner\PartnerNotificationHandler;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\Partner\PartnerStatuses;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Sms\Sms;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class BusinessesController extends Controller
{
    use ModificationFields;

    const DIGIGO_PORTAL = 'digigo-portal';
    private $digigo_management_emails = ['one' => 'b2b@sheba.xyz'];
    /** @var AccountServer */
    private $accountServer;

    /**
     * BusinessesController constructor.
     * @param AccountServer $account_server
     */
    public function __construct(AccountServer $account_server)
    {
        $this->accountServer = $account_server;
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function inviteVendors($business, Request $request)
    {
        $this->validate($request, ['numbers' => 'required|json']);
        $business = $request->business;
        $this->setModifier($business);
        $invited_vendor = 0;
        $added_vendor = 0;

        foreach (json_decode($request->numbers) as $number) {
            $mobile = formatMobile($number);
            if ($partner = $this->hasPartner($mobile)) {
                $partner->businesses()->sync(['business_id' => $business->id]);
                $added_vendor++;
            } else {
                $data = ['business_id' => $business->id, 'mobile' => $mobile];
                BusinessJoinRequest::create($data);
                $invited_vendor++;
                $message = "You have been invited to serve corporate client. Just click the link- http://bit.ly/ShebaManagerApp. 
                    sheba.xyz will help you to grow and manage your business. by $business->name";
                (new Sms())
                    ->setFeatureType(FeatureType::INVITE_VENDORS)
                    ->setBusinessType(BusinessType::B2B)
                    ->shoot($number, $message);
            }
        }

        return api_response($request, null, 200);
    }

    /**
     * @param $mobile
     * @return false|mixed
     */
    private function hasPartner($mobile)
    {
        $profile = Profile::where('mobile', $mobile)->first();
        if (!$profile) return false;
        /** @var Resource $resource */
        $resource = $profile->resource;
        if (!$resource) return false;
        $partner = $resource->firstPartner();

        return $partner ?: false;
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function getVendorsList($business, Request $request)
    {
        $business = $request->business;
        if (!$business) return api_response($request, null, 404);

        list($offset, $limit) = calculatePagination($request);
        $partners = $business->partners()->select('id', 'name', 'mobile', 'logo', 'address', 'is_active_for_b2b')->with([
            'categories' => function ($q) {
                $q->select('categories.id', 'parent_id', 'name')->with([
                    'parent' => function ($q) {
                        $q->select('id', 'parent_id', 'name');
                    }
                ]);
            }, 'resources.profile'
        ]);
        $is_business_has_vendors = $partners->count() ? 1 : 0;

        if ($request->has('status')) $partners = $partners->where('is_active_for_b2b', $request->status);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $vendors = new Collection($partners->get(), new VendorListTransformer());
        $vendors = $manager->createData($vendors)->toArray()['data'];

        $vendors = collect($vendors);
        if ($request->has('search')) $vendors = $this->searchWithName($vendors, $request);
        $total_vendors = $vendors->count();
        if ($request->has('limit')) $vendors = $vendors->splice($offset, $limit);

        return api_response($request, $vendors, 200, [
            'vendors' => $vendors,
            'total_vendors' => $total_vendors,
            'is_business_has_vendors' => $is_business_has_vendors
        ]);
    }

    /**
     * @param $vendors
     * @param Request $request
     * @return mixed
     */
    private function searchWithName($vendors, Request $request)
    {
        return $vendors->filter(function ($vendor) use ($request) {
            return str_contains(strtoupper($vendor['name']), strtoupper($request->search));
        });
    }

    /**
     * @param $business
     * @param $vendor
     * @param Request $request
     * @return JsonResponse
     */
    public function getVendorInfo($business, $vendor, Request $request)
    {
        $business = $request->business;
        /** @var Partner $partner */
        $partner = Partner::find((int)$vendor);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $vendor = new Item($partner, new VendorDetailsTransformer());
        $vendor = $manager->createData($vendor)->toArray()['data'];

        return api_response($request, $vendor, 200, ['vendor' => $vendor]);
    }

    /**
     * @param $business
     * @param $vendor
     * @param Request $request
     * @return JsonResponse
     */
    public function getVendorAdminInfo($business, $vendor, Request $request)
    {
        $partner = Partner::find((int)$vendor);
        if (!$partner) return api_response($request, null, 404);
        $resource = $partner->admins->first();
        if (!$resource) return api_response($request, null, 404);

        $resource = [
            "id" => $resource->id,
            "name" => $resource->profile->name,
            "mobile" => $resource->profile->mobile,
            "email" => $resource->profile->email,
            "nid" => $resource->profile->nid_no,
            "nid_image_front" => $resource->profile->nid_image_front ?: $resource->nid_image,
            "nid_image_back" => $resource->profile->nid_image_back
        ];
        return api_response($request, null, 200, ['vendor' => $resource]);
    }

    public function getNotifications($business, Request $request)
    {
        $business = $request->business;
        $manager_member = $request->manager_member;

        list($offset, $limit) = calculatePagination($request);
        $all_notifications = Notification::where('notifiable_type', 'App\Models\Member')
            ->where('notifiable_id', (int)$manager_member->id)
            ->whereIn('event_type', [
                'App\Models\Procurement',
                'App\Models\Bid',
                'App\Models\Driver',
                'App\Models\Vehicle',
                'Sheba\Dal\Support\Model',
                'App\Models\BusinessTripRequest',
                'Sheba\Dal\Leave\Model',
            ])
            ->skip($offset)->limit($limit)
            ->orderBy('id', 'DESC');

        $notifications = [];
        foreach ($all_notifications->get() as $notification) {
            $image = $this->getImage($notification);
            array_push($notifications, [
                "id" => $notification->id,
                "image" => $image,
                "title" => $notification->title,
                "is_seen" => $notification->is_seen,
                "event_type" => $notification->getType(),
                "link" => $notification->link,
                "event_id" => $notification->event_id,
                "created_at" => $notification->created_at->format('M d h:ia')
            ]);
        }
        return api_response($request, $notifications, 200, ['notifications' => $notifications]);
    }

    private function getImage($notification)
    {
        $event_type = $notification->event_type;
        $model = $event_type::find((int)$notification->event_id);
        $image = '';
        if (class_basename($model) == 'Driver') {
            $image = $model->profile->pro_pic;
        }
        if (class_basename($model) == 'Vehicle') {
            $image = $model->basicInformation->vehicle_image;
        }
        return $image;
    }

    public function notificationSeen($business, $notification, Request $request)
    {
        try {
            $notification = Notification::find((int)$notification);
            if ($notification->is_seen == 1)
                return api_response($request, null, 403, ['message' => 'This notification already seen']);
            $notification->seen();
            $business = $request->business;
            $this->setModifier($business);
            return api_response($request, null, 200);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param TimeFrameReportRequest $request
     * @param ExcelHandler $excel
     * @param TransactionReportData $data
     * @throws NotAssociativeArray
     * @throws Exception
     */
    public function downloadTransactionReport($business, TimeFrameReportRequest $request, ExcelHandler $excel, TransactionReportData $data)
    {
        if (!$request->isLifetime()) $data->setTimeFrame($request->getTimeFrame());
        $business = $request->business instanceof Business ? $request->business : Business::find((int)$request->business);
        $data->setBusiness($business);
        $excel->setName('Transactions')
            ->createReport($data->get())
            ->download();
    }

    public function contactUs(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
            'portal' => 'sometimes|required|in:' . self::DIGIGO_PORTAL
        ]);

        if ($request->portal == self::DIGIGO_PORTAL) {
            foreach ($this->digigo_management_emails as $management_email) {
                $this->sendMail($request->message, $request->email, $request->name, $management_email);
            }
        } else {
            $this->sendMail($request->message, $request->email, $request->name);
        }
        return api_response($request, null, 200);
    }

    public function getBanks(Request $request)
    {
        $banks = [];
        foreach (array_values(GeneralBanking::getWithKeys()) as $key => $bank) {
            array_push($banks, [
                'key' => $bank,
                'value' => ucwords(str_replace('_',' ',$bank)),
            ]);
        }
        return api_response($request, null, 200, ['banks' => $banks]);
    }

    private function sendMail($message, $email, $name, $to = 'b2b@sheba.xyz')
    {
        Mail::raw($message, function ($m) use ($email, $name, $to) {
            $m->from($email, $name);
            $m->to($to);
            $m->subject('Contact Us');
        });
    }

    /**
     * @param $business
     * @param Request $request
     * @param ProfileRepositoryInterface $profile_repository
     * @return JsonResponse
     */
    public function getVendorsListV3($business, Request $request, ProfileRepositoryInterface $profile_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 404);

        $vendors = collect();
        $sheba_verified_vendors = collect();

        $business->activePartners()
            ->with('resources.profile')
            ->select('id', 'name', 'logo', 'address')
            ->get()
            ->each(function ($partner) use ($vendors) {
                $vendor = [
                    "id"    => $partner->id,
                    "name"  => $partner->name,
                    "logo"  => $partner->logo,
                    "mobile"=> $partner->getContactNumber()
                ];

                $vendors->push($vendor);
            });

        if ($request->has('q')) {
            $needle = $request->q;
            $vendors = $vendors->filter(function ($vendor) use ($needle) {
                return (stripos($vendor['mobile'], $needle) !== false) ||
                    (stripos($vendor['name'], $needle) !== false);
            })->values();

            if ($vendors->isEmpty()) {
                $mobile_validator = BangladeshiMobileValidator::validate($request->q);
                if (!$mobile_validator)
                    return api_response($request, null, 400, [
                        'own_vendors' => $vendors,
                        'sheba_verified_vendors' => $sheba_verified_vendors,
                        'message' => 'Mobile number not proper bangladeshi number. Give a proper bangladeshi number, like 01678242973'
                    ]);

                $mobile = formatMobile($request->q);
                /** @var Partner $partner */
                $partner = $this->getPartner($profile_repository, $mobile);
                if ($partner)
                    $sheba_verified_vendors->push([
                        'id' => $partner->id,
                        'name' => $partner->name,
                        'logo' => $partner->logo,
                        'mobile' => $partner->getContactNumber()
                    ]);
            }
        }

        return api_response($request, null, 200, ['own_vendors' => $vendors, 'sheba_verified_vendors' => $sheba_verified_vendors]);
    }

    /**
     * @param ProfileRepositoryInterface $profile_repository
     * @param $mobile
     * @return mixed|null
     */
    private function getPartner(ProfileRepositoryInterface $profile_repository, $mobile)
    {
        /** @var Profile $profile */
        $profile = $profile_repository->findByMobile($mobile)->first();
        if (!$profile || !$profile->resource) return null;

        /** @var Resource $resource */
        $resource = $profile->resource;
        if (!$resource->firstPartner()) return null;

        $partner = $resource->firstPartner();
        if ($partner->status != PartnerStatuses::VERIFIED) return null;

        return $partner;
    }

    /**
     * @param Request $request
     * @param MemberRepositoryInterface $member_repository
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function getTopUpPortalToken(Request $request, MemberRepositoryInterface $member_repository)
    {
        /** @var Member $member */
        $member = $this->getMember($member_repository);
        $verification_token = randomString(30, 1, 1);
        $top_up_jwt_token = ['jwt_token' => $this->fetchJWTToken($member)];
        $redis_name_space = 'TopUpPortal::topup-portal_' . $verification_token;
        Redis::set($redis_name_space, json_encode($top_up_jwt_token));
        return api_response($request, null, 200, ['verification_token' => $verification_token]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tokenVerify(Request $request)
    {
        $this->validate($request, ['verification_token' => 'required']);
        $redis_name_space = 'TopUpPortal::topup-portal_' . $request->verification_token;
        $verification_token = Redis::get($redis_name_space);
        if (!$verification_token) return api_response($request, null,400, ['message' => 'Code do not match']);
        $verification_token = json_decode($verification_token, 1);
        Redis::del(Redis::keys($redis_name_space));
        return api_response($request, null, 200,['token' => $verification_token['jwt_token']]);
    }

    /**
     * @param MemberRepositoryInterface $member_repository
     * @return mixed
     */
    private function getMember(MemberRepositoryInterface $member_repository)
    {
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();
        return $member_repository->find($payload['member']['id']);
    }

    /**
     * @param Member $member
     * @return mixed
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function fetchJWTToken(Member $member)
    {
        return $this->accountServer->getTokenByIdAndRememberToken($member->id, $member->remember_token, 'member');
    }
}
