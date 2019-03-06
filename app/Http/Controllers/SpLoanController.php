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
            $bank_informations = $partner->bankInformations;

            #dd($partner, $manager_resource, $profile, $basic_informations);
            $info = array(
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'gender' => $profile->gender,
                'picture' => $profile->pro_pic,
                'birthday' => $profile->dob,
                'present_address' => $profile->address,
                'permanent_address' =>$profile->permanent_address,
                'father_name' => $manager_resource->father_name,
                'spouse_name' => $manager_resource->spouse_name,
                'occupation_lists' => constants('SUGGESTED_OCCUPATION'),
                'occupation' => $profile->occupation,
                'expenses' => [
                    'monthly_living_cost' => $profile->monthly_living_cost,
                    'total_asset_amount' => $profile->total_asset_amount,
                    'monthly_loan_installment_amount' => $profile->monthly_loan_installment_amount,
                    'utility_bill_attachment' => $profile->utility_bill_attachment
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
                'business_type' => $partner->business_type,
                'location' => $partner->address,
                'establishment_year' => $basic_informations->establishment_year,
                'full_time_employee' => $partner->full_time_employee,
                'part_time_employee' => $partner->part_time_employee,
                'business_expenses' => [
                    'product_price' => 100,
                    'employee_salary' => 100,
                    'office_rent' => 100,
                    'utility_bills' => 100,
                    'marketing_cost' => 100,
                    'other_costs' => 100
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

    public function getFinanceInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            $info = array(
                'account_holder_name' => $bank_informations->acc_name,
                'account_no' => $basic_informations->acc_no,
                'bank_name' => $partner->bank_name,
                'brunch' => $basic_informations->branch_name,
                'account_type' => "Change",
                'bkash' => [
                    'account_no' => $partner->bkash_no,
                    'account_type' => "Change"
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNomineeInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            $info = array(
                'name' => $bank_informations->acc_name,
                'mobile' => $basic_informations->acc_no,
                'relation' => $partner->bank_name,
                'picture' => $profile->pro_pic,
                'nid_front_image' => $manager_resource->nid_image,
                'nid_back_image' => $manager_resource->nid_image,
                'granter' => [
                    'name' => $bank_informations->acc_name,
                    'mobile' => $basic_informations->acc_no,
                    'relation' => $partner->bank_name,
                    'picture' => $profile->pro_pic,
                    'nid_front_image' => $manager_resource->nid_image,
                    'nid_back_image' => $manager_resource->nid_image,
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDocuments($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            $info = array(
                'picture' => $profile->pro_pic,
                'nid_front_image' => $manager_resource->nid_image,
                'nid_back_image' => $manager_resource->nid_image,
                'birth_certificate' => $manager_resource->nid_image,
                'nominee_document' => [
                    'picture' => $profile->pro_pic,
                    'nid_front_image' => $manager_resource->nid_image,
                    'nid_back_image' => $manager_resource->nid_image,
                    'birth_certificate' => $manager_resource->nid_image,
                ],
                'business_document' => [
                    'tin_no_attachment' => $profile->tin_no,
                    'trade_license_attachment' => $basic_informations->trade_license_attachment,
                    'bank_statement_attachment' => $partner->nid_image
                ],

            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}