<?php namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Validation\ValidationException;
use App\Repositories\FileRepository;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class SpLoanInformationCompletion extends Controller
{
    public function getLoanInformationCompletion($partner, Request $request)
    {
        try {
            $complete_count = 0;

            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            $completion = [
                'personal' => $this->personalInformationCompletion($profile, $manager_resource, $complete_count),
                'business' => $this->businessInformationCompletion($partner, $basic_informations, $complete_count),
                'finance' =>  $this->financeInformationCompletion($partner, $bank_informations, $complete_count),
                'nominee' =>  $this->nomineeInformationCompletion($profile, $complete_count),
                'documents' => $this->documentCompletion($profile, $partner, $complete_count)
            ];

            return api_response($request, $completion, 200, ['completion' => $completion]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function personalInformationCompletion($profile, $manager_resource, $complete_count)
    {
        if (!empty($profile->name)) $complete_count++;
        if (!empty($profile->mobile)) $complete_count++;
        if (!empty($profile->gender)) $complete_count++;
        if (!empty($profile->pro_pic)) $complete_count++;
        if (!empty($profile->dob)) $complete_count++;
        if (!empty($profile->address)) $complete_count++;
        if (!empty($profile->permanent_address)) $complete_count++;
        if (!empty($manager_resource->father_name)) $complete_count++;
        if (!empty($manager_resource->spouse_name)) $complete_count++;
        if (!empty($profile->occupation)) $complete_count++;
        if (!empty($profile->monthly_living_cost)) $complete_count++;
        if (!empty($profile->total_asset_amount)) $complete_count++;
        if (!empty($profile->monthly_loan_installment_amount)) $complete_count++;
        if (!empty($profile->utility_bill_attachment)) $complete_count++;

        $personal_information = round((($complete_count / 14) * 100), 0);
        return $personal_information;
    }

    private function businessInformationCompletion($partner, $basic_informations, $complete_count)
    {
        $business_additional_information = $partner->businessAdditionalInformation()['0'];
        $sales_information = $partner->salesInformation()['0'];

        if (!empty($partner->name)) $complete_count++;
        if (!empty($partner->business_type)) $complete_count++;
        if (!empty($partner->address)) $complete_count++;
        if (!empty($basic_informations->establishment_year)) $complete_count++;
        if (!empty($partner->full_time_employee)) $complete_count++;
        if (!empty($partner->part_time_employee)) $complete_count++;
        if (count((array)$business_additional_information) >= 6) $complete_count++;
        if (count((array)$sales_information) >= 3) $complete_count++;
        $business_information = round((($complete_count / 8) * 100), 0);
        return $business_information;
    }

    private function financeInformationCompletion($partner, $bank_informations, $complete_count)
    {
        if(!empty($bank_informations->acc_name)) $complete_count++;
        if(!empty($bank_informations->acc_no)) $complete_count++;
        if(!empty($bank_informations->bank_name)) $complete_count++;
        if(!empty($bank_informations->branch_name)) $complete_count++;
        if(!empty($bank_informations->acc_type)) $complete_count++;
        if(!empty($partner->bkash_no)) $complete_count++;
        if(!empty($partner->bkash_account_type)) $complete_count++;

        $finance_information = round((($complete_count / 7) * 100), 0);
        return $finance_information;
    }

    private function nomineeInformationCompletion($profile, $complete_count)
    {
        $nominee_profile = Profile::find($profile->nominee_id);
        $grantor_profile = Profile::find($profile->grantor_id);

        if ($nominee_profile){
            if (!(empty($nominee_profile->name ))) $complete_count++;
            if (!(empty($nominee_profile->mobile ))) $complete_count++;
            if (!(empty($profile->nominee_relation ))) $complete_count++;
            if (!(empty($nominee_profile->pro_pic ))) $complete_count++;
            if (!(empty($nominee_profile->nid_front_image ))) $complete_count++;
            if (!(empty($nominee_profile->nid_back_image ))) $complete_count++;
        }
        if ($grantor_profile){
            if (!(empty($grantor_profile->name ))) $complete_count++;
            if (!(empty($grantor_profile->mobile ))) $complete_count++;
            if (!(empty($profile->nominee_relation ))) $complete_count++;
            if (!(empty($grantor_profile->pro_pic ))) $complete_count++;
            if (!(empty($grantor_profile->nid_front_image ))) $complete_count++;
            if (!(empty($grantor_profile->nid_back_image ))) $complete_count++;
        }

        $nominee_information = round((($complete_count / 12) * 100), 0);
        return $nominee_information;
    }

    private function documentCompletion($profile, $partner, $complete_count)
    {
        $basic_informations = $partner->basicInformations;
        $bank_informations = $partner->bankInformations;
        $nominee_profile = Profile::find($profile->nominee_id);

        if (!empty($profile->pro_pic)) $complete_count++;
        if (!empty($profile->nid_front_image)) $complete_count++;
        if (!empty($profile->nid_back_image)) $complete_count++;

        if ($nominee_profile){
            if (!(empty($nominee_profile->pro_pic ))) $complete_count++;
            if (!(empty($nominee_profile->nid_front_image ))) $complete_count++;
            if (!(empty($nominee_profile->nid_back_image ))) $complete_count++;

        }

        if (!empty($profile->tin_certificate)) $complete_count++;
        if (!empty($basic_informations->trade_license_attachment)) $complete_count++;
        if (!empty($bank_informations->statement)) $complete_count++;

        $documents = round((($complete_count / 9) * 100), 0);
        return $documents;
    }

}