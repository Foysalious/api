<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessJoinRequest;
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
                $data = [
                    'business_id' => $business->id,
                    'mobile' => formatMobile($number)
                ];
                BusinessJoinRequest::create($data);
                $this->sms->shoot($number, "You have been invited to Sheba.xyz by $business->name");
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

    public function getVendorsInfo($business, Request $request)
    {
        try {
            $business = $request->business;
            $partners = $business->partners()->with('categories')->select('id', 'name', 'mobile')->get();
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
                        "mobile" => $partner->mobile,
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
}