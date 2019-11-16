<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessJoinRequest;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Member;
use Carbon\Carbon;
use DB;
use Sheba\Sms\Sms;

class BusinessesController extends Controller
{
    use ModificationFields;
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function inviteVendors($business, Request $request)
    {
        try {
            $this->validate($request, [
                'numbers' => 'required|json'
            ]);

            $business = $request->business;
            $this->setModifier($business);

            foreach (json_decode($request->numbers) as $number) {

                $mobile = formatMobile($number);
                if ($partner = $this->hasPartner($mobile)) {
                    $partner->businesses()->sync(['business_id' => $business->id]);
                } else {
                    $data = [
                        'business_id' => $business->id,
                        'mobile' => $mobile
                    ];
                    BusinessJoinRequest::create($data);
                    $this->sms->shoot($number, "You have been invited to serve corporate client. Just click the link- http://bit.ly/ShebaManagerApp . sheba.xyz will help you to grow and manage your business. by $business->name");
                }
            }
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function hasPartner($mobile)
    {
        $profile = Profile::where('mobile', $mobile)->first();
        if (!$profile) return false;
        /** @var Resource $resource */
        $resource = $profile->resource;
        if (!$resource) return false;
        $partner = $resource->firstPartner();
        return $partner ? $partner : false;
    }

    public function getVendorsList($business, Request $request)
    {
        try {
            $business = $request->business;
            $partners = $business->partners()->with('categories')->select('id', 'name', 'mobile', 'logo', 'address')->get();
            $vendors = collect();
            if ($business) {
                foreach ($partners as $partner) {
                    $master_categories = collect();
                    $partner->categories->map(function ($category) use ($master_categories) {
                        $parent_category = $category->parent()->select('id', 'name')->first();
                        $master_categories->push($parent_category);
                    });
                    $master_categories = $master_categories->unique()->pluck('name');
                    $vendor = [
                        "id" => $partner->id,
                        "name" => $partner->name,
                        "logo" => $partner->logo,
                        "address" => $partner->address,
                        "mobile" => $partner->getContactNumber(),
                        'type' => $master_categories
                    ];
                    $vendors->push($vendor);
                }
                return api_response($request, $vendors, 200, ['vendors' => $vendors]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVendorInfo($business, $vendor, Request $request)
    {
        try {
            $business = $request->business;
            /** @var Partner $partner */
            $partner = Partner::find((int)$vendor);
            $basic_informations = $partner->basicInformations;
            $resources = $partner->resources->count();
            $type = $partner->businesses->pluck('type')->unique();

            $master_categories = collect();
            $partner->categories->map(function ($category) use ($master_categories) {
                $parent_category = $category->parent()->select('id', 'name')->first();
                $master_categories->push($parent_category);
            });
            $master_categories = $master_categories->unique()->pluck('name');

            $vendor = [
                "id" => $partner->id,
                "name" => $partner->name,
                "logo" => $partner->logo,
                "mobile" => $partner->getContactNumber(),
                "company_type" => $type,
                "service_type" => $master_categories,
                "no_of_resource" => $resources,
                "trade_license" => $basic_informations->trade_license,
                "trade_license_attachment" => $basic_informations->trade_license_attachment,
                "vat_registration_number" => $basic_informations->vat_registration_number,
                "vat_registration_attachment" => $basic_informations->vat_registration_attachment,
                "establishment_year" => $basic_informations->establishment_year ? Carbon::parse($basic_informations->establishment_year)->format('M, Y') : null,
            ];
            return api_response($request, $vendor, 200, ['vendor' => $vendor]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getVendorAdminInfo($business, $vendor, Request $request)
    {
        try {
            $partner = Partner::find((int)$vendor);
            $resource = $partner->admins->first();
            $resource = [
                "id" => $resource->id,
                "name" => $resource->profile->name,
                "mobile" => $resource->profile->mobile,
                "nid" => $resource->profile->nid_no,
                "nid_image_front" => $resource->profile->nid_image_front ?: $resource->nid_image,
                "nid_image_back" => $resource->profile->nid_image_back
            ];
            return api_response($request, $resource, 200, ['vendor' => $resource]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNotifications($business, Request $request)
    {
        try {
            $business = $request->business;
            $manager_member = $request->manager_member;
            $notifications = [
                [
                    "id" => 1,
                    "image" => 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/profiles/vehicles/1562679528_vehicle_image_126.jpeg',
                    "title" => 'Fitness Paper of your vehicle DM-Cho-16-052 is Due Soon. 30 Days from now.',
                    "is_seen" => '0',
                    "created_at" => 'Mar 15 02:30PM'
                ],
                [
                    "id" => 2,
                    "image" => 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/profiles/vehicles/1562679528_vehicle_image_126.jpeg',
                    "title" => 'Fitness Paper of your vehicle DM-Cho-16-052 is already in Over due.',
                    "is_seen" => '1',
                    "created_at" => 'Mar 15 02:30PM'
                ]
            ];
            return api_response($request, $notifications, 200, ['notifications' => $notifications]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function notificationSeen($business, $notification, Request $request)
    {
        try {
            $this->validate($request, [
                'is_seen' => 'required|in:0,1'
            ]);

            $business = $request->business;
            $this->setModifier($business);

            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
