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
            $bank_informations  = $partner->bankInformations ? $partner->bankInformations->first() : null;

            $personal = $this->personalInformationCompletion($profile, $manager_resource, $complete_count);
            $business = $this->businessInformationCompletion($partner, $basic_informations, $complete_count);
            $finance = $this->financeInformationCompletion($partner, $bank_informations, $complete_count);
            $nominee = $this->nomineeInformationCompletion($profile, $complete_count);
            $documents = $this->documentCompletion($profile, $manager_resource, $partner, $complete_count);

            $is_all_completed = (($personal['personal_information'] >= 50) && ($business['business_information'] >= 20) && ($finance['finance_information'] >= 70) && ($nominee['nominee_information'] == 100) && ($documents['documents'] >= 50)) ? 1 : 0;

            $completion = [
                'personal' => [
                    'completion_percentage' => $personal['personal_information'],
                    'last_update' => $personal['last_update']
                ],
                'business' => [
                    'completion_percentage' => $business['business_information'],
                    'last_update' => $business['last_update']
                ],
                'finance' => [
                    'completion_percentage' => $finance['finance_information'],
                    'last_update' => $finance['last_update']
                ],
                'nominee' => [
                    'completion_percentage' => $nominee['nominee_information'],
                    'last_update' => $nominee['last_update']
                ],
                'documents' => [
                    'completion_percentage' => $documents['documents'],
                    'last_update' => $documents['last_update']
                ],
                'is_applicable_for_loan' => $is_all_completed
            ];

            return api_response($request, $completion, 200, ['completion' => $completion]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function personalInformationCompletion($profile, $manager_resource, $complete_count)
    {
        $update_at = collect();
        if (!empty($profile->name)) $complete_count++;
        if (!empty($profile->mobile)) $complete_count++;
        if (!empty($profile->gender)) $complete_count++;
        #if (!empty($profile->pro_pic)) $complete_count++;
        if (!empty($profile->dob)) $complete_count++;
        if (!empty($profile->address)) $complete_count++;
        if (!empty($profile->permanent_address)) $complete_count++;
        if (!empty($profile->occupation)) $complete_count++;

        if (!empty((int)$profile->monthly_living_cost)) $complete_count++;
        if (!empty((int)$profile->total_asset_amount)) $complete_count++;
        #if (!empty((int)$profile->monthly_loan_installment_amount)) $complete_count++;

        if (!empty($profile->utility_bill_attachment)) $complete_count++;
        $update_at->push($profile->updated_at);

        if (!empty($manager_resource->father_name)) {
            $complete_count++;
        } else {
            if (!empty($manager_resource->spouse_name)) $complete_count++;
        }

        $update_at->push($manager_resource->updated_at);

        $last_update = getDayName($update_at->max());

        $personal_information = round((($complete_count / 11) * 100), 0);
        return ['personal_information' => $personal_information, 'last_update' => $last_update];
    }

    private function businessInformationCompletion($partner, $basic_informations, $complete_count)
    {
        $business_additional_information = $partner->businessAdditionalInformation();
        $sales_information = $partner->salesInformation();
        $update_at = collect();

        if (!empty($partner->name)) $complete_count++;
        if (!empty($partner->business_type)) $complete_count++;
        if (!empty($partner->address)) $complete_count++;
        if (!empty($partner->full_time_employee)) $complete_count++;
        #if (!empty($partner->part_time_employee)) $complete_count++;
        $update_at->push($partner->updated_at);

        if (!empty($basic_informations->establishment_year)) $complete_count++;
        $update_at->push($basic_informations->updated_at);
        if (count((array)$business_additional_information) >= 2) $complete_count++;
        if (count((array)$sales_information) >= 3) $complete_count++;

        $last_update = getDayName($update_at->max());

        $business_information = round((($complete_count / 7) * 100), 0);
        return ['business_information' => $business_information, 'last_update' => $last_update];
    }

    private function financeInformationCompletion($partner, $bank_informations, $complete_count)
    {
        $update_at = collect();
        if ($bank_informations) {
            if (!empty($bank_informations->acc_name)) $complete_count++;
            if (!empty($bank_informations->acc_no)) $complete_count++;
            if (!empty($bank_informations->bank_name)) $complete_count++;
            if (!empty($bank_informations->branch_name)) $complete_count++;
            if (!empty($bank_informations->acc_type)) $complete_count++;
            $update_at->push($bank_informations->updated_at);
        }

        if (!empty($partner->bkash_no)) $complete_count++;
        if (!empty($partner->bkash_account_type)) $complete_count++;
        $update_at->push($partner->updated_at);

        $last_update = getDayName($update_at->max());

        $finance_information = round((($complete_count / 7) * 100), 0);
        return ['finance_information' => $finance_information, 'last_update' => $last_update];
    }

    private function nomineeInformationCompletion($profile, $complete_count)
    {
        #$nominee_profile = Profile::find($profile->nominee_id);
        $grantor_profile = Profile::find($profile->grantor_id);
        $update_at = collect();

        /*if ($nominee_profile) {
            if (!(empty($nominee_profile->name))) $complete_count++;
            if (!(empty($nominee_profile->mobile))) $complete_count++;
            if (!(empty($profile->nominee_relation))) $complete_count++;
            $update_at->push($nominee_profile->updated_at);

        }*/
        if ($grantor_profile) {
            if (!(empty($grantor_profile->name))) $complete_count++;
            if (!(empty($grantor_profile->mobile))) $complete_count++;
            $update_at->push($grantor_profile->updated_at);
            if (!(empty($profile->grantor_relation))) $complete_count++;
            $update_at->push($profile->updated_at);
        }
        if ($grantor_profile) {
            $last_update = getDayName($update_at->max());
        } else {
            $last_update = 0;
        }

        $nominee_information = round((($complete_count / 3) * 100), 0);

        return ['nominee_information' => $nominee_information, 'last_update' => $last_update];
    }

    private function documentCompletion($profile, $manager_resource, $partner, $complete_count)
    {
        $basic_informations = $partner->basicInformations;
        $bank_informations = $partner->bankInformations ? $partner->bankInformations->first() : null;
        #$nominee_profile = Profile::find($profile->nominee_id);
        $grantor_profile = Profile::find($profile->grantor_id);

        $update_at = collect();

        if (!empty($profile->pro_pic)) $complete_count++;
        if (!empty($manager_resource->nid_image)) $complete_count += 2;
        else {
            if (!empty($profile->nid_image_front)) $complete_count++;
            if (!empty($profile->nid_image_back)) $complete_count++;
        }


        $update_at->push($profile->updated_at);

        /*if ($nominee_profile) {
            if (!(empty($nominee_profile->pro_pic))) $complete_count++;
            if (!(empty($nominee_profile->nid_image_front))) $complete_count++;
            if (!(empty($nominee_profile->nid_image_back))) $complete_count++;
            $update_at->push($nominee_profile->updated_at);
        }*/

        if ($grantor_profile) {
            if (!(empty($grantor_profile->pro_pic))) $complete_count++;
            if (!(empty($grantor_profile->nid_image_front))) $complete_count++;
            if (!(empty($grantor_profile->nid_image_back))) $complete_count++;
            $update_at->push($grantor_profile->updated_at);
        }

        if (!empty($profile->tin_certificate)) $complete_count++;
        if (!empty($basic_informations->trade_license_attachment)) $complete_count++;
        $update_at->push($basic_informations->updated_at);
        /*if ($bank_informations) {
            if (!empty($bank_informations->statement)) $complete_count++;
            $update_at->push($bank_informations->updated_at);
        }*/

        $last_update = getDayName($update_at->max());

        $documents = round((($complete_count / 8) * 100), 0);

        return ['documents' => $documents, 'last_update' => $last_update];
    }

}
