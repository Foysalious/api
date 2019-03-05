<?php
namespace App\Http\Controllers;

use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\ProfileRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class SpLoanController extends Controller
{
    public function getPersonalInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;

            #dd($partner, $manager_resource, $profile, $basic_informations);
            $info = array(
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'gender' => $profile->gender,
                'picture' => $profile->pro_pic,
                'birthday' => $profile->dob,
                'present_address' => $profile->address,
                'permanent_address' => "Change",
                'father_name' => $profile->father_name,
                'spouse_name' => $profile->spouse_name,
                'husband_name' => "Change",
                'profession' => $profile->profession,
                'expenses' => [
                    'family_cost_per_month' => "Change",
                    'cost_per_month' => "Change",
                    'total_asset' => "Change",
                    'other_loan_installments_per_month' => "Change",
                    'utility_bill' => "Change"
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBusinessInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            #dd($partner, $manager_resource, $profile, $basic_informations);
            $info = array(
                'business_name' => $partner->name,
                'business_type' => $basic_informations->company_type,
                'location' => $partner->address,
                'establishment_year' => $basic_informations->establishment_year,
                'full_time_employee' => 10,
                'part_time_employee' => 100,
                'business_expenses' => [
                    'employee_salary' => 100,
                    'office_rent' => 100,
                    'utility_bills' => 100,
                    'marketing_cost' => 100,
                    'other_costs' => 100,
                    'price' => 100
                ],
                'last_six_month_sell' => [
                    'avg_sell' => 100,
                    'min_sell' => 100,
                    'max_sell' => 100
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}