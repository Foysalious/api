<?php namespace App\Http\Controllers\B2b;

use App\Http\Requests\TimeFrameReportRequest;
use App\Models\BusinessJoinRequest;
use App\Models\BusinessMember;
use App\Models\Notification;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Sheba\BankingInfo\GeneralBanking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\Business\TransactionReportData;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Business;
use Carbon\Carbon;
use DB;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Sms\Sms;

class BusinessesController extends Controller
{
    use ModificationFields;
    const DIGIGO_PORTAL = 'digigo-portal';
    private $digigo_management_emails = [
        'one' => 'khairun@sheba.xyz'
    ];
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
        } catch (\Throwable $e) {
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
     * @throws \Exception
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
}
