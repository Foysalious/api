<?php namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Validation\ValidationException;
use App\Repositories\FileRepository;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class SpLoanController extends Controller
{
    use ModificationFields;

    private $fileRepository;

    public function __construct(FileRepository $file_repository)
    {
        $this->fileRepository = $file_repository;
    }

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
                'genders' => constants('GENDER'),
                'picture' => $profile->pro_pic,
                'birthday' => $profile->dob,
                'present_address' => $profile->address,
                'permanent_address' => $profile->permanent_address,
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

    public function updatePersonalInformation($partner, Request $request)
    {
        try {

            $manager_resource = $request->manager_resource;

            $profile = $manager_resource->profile;
            $profile_data = array(
                'gender' => $request->gender,
                'dob' => $request->dob,
                'address' => $request->address,
                'permanent_address' => $request->permanent_address,
                'occupation' => $request->occupation,
                'monthly_living_cost' => $request->monthly_living_cost,
                'total_asset_amount' => $request->total_asset_amount,
                'monthly_loan_installment_amount' => $request->monthly_loan_installment_amount,
            );
            $resource_data = [
                'father_name' => $request->father_name,
                'spouse_name' => $request->spouse_name,
            ];
            $profile->update($this->withBothModificationFields($profile_data));
            $manager_resource->update($this->withBothModificationFields($resource_data));
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
            $business_additional_information = $partner->businessAdditionalInformation()['0'];
            $sales_information = $partner->salesInformation()['0'];
            #dd($business_additional_information, $sales_information);

            $info = array(
                'business_name' => $partner->name,
                'business_type' => $partner->business_type,
                'location' => $partner->address,
                'establishment_year' => $basic_informations->establishment_year,
                'full_time_employee' => $partner->full_time_employee,
                'part_time_employee' => $partner->part_time_employee,
                'business_additional_information' => [
                    'product_price' => $business_additional_information->product_price,
                    'employee_salary' => $business_additional_information->employee_salary,
                    'office_rent' => $business_additional_information->office_rent,
                    'utility_bills' => $business_additional_information->utility_bills,
                    'marketing_cost' => $business_additional_information->marketing_cost,
                    'other_costs' => $business_additional_information->other_costs
                ],
                'last_six_month_sales_information' => [
                    'avg_sell' => $sales_information->last_six_month_avg_sell,
                    'min_sell' => $sales_information->last_six_month_min_sell,
                    'max_sell' => $sales_information->last_six_month_max_sell
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateBusinessInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $basic_informations = $partner->basicInformations;
            $partner_data = [
                'business_type' => $request->business_type,
                'address' => $request->address,
                'full_time_employee' => $request->full_time_employee,
                'part_time_employee' => $request->part_time_employee,
                'sales_information' => $request->sales_information,
                'business_additional_information' => $request->business_additional_information,
            ];
            $partner_basic_data = [
                'establishment_year' => $request->establishment_year,
            ];

            $partner->update($this->withBothModificationFields($partner_data));
            $basic_informations->update($this->withBothModificationFields($partner_basic_data));
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
                'account_no' => $bank_informations->acc_no,
                'bank_name' => $bank_informations->bank_name,
                'brunch' => $bank_informations->branch_name,
                'acc_type' => $bank_informations->acc_type,
                'acc_types' => constants('BANK_ACCOUNT_TYPE'),
                'bkash' => [
                    'bkash_no' => $partner->bkash_no,
                    'bkash_account_type' => $partner->bkash_account_type,
                    'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE')
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateFinanceInformation($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $bank_informations = $partner->bankInformations;

            $bank_data = [
                'acc_name' => $request->acc_name,
                'acc_no' => $request->acc_no,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_type' => $request->acc_type
            ];
            $partner_data = [
                'bkash_no' => formatMobile($request->bkash_no),
                'bkash_account_type' => $request->bkash_account_type
            ];

            $bank_informations->update($this->withBothModificationFields($bank_data));
            $partner->update($this->withBothModificationFields($partner_data));
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getNomineeInformation($partner, Request $request)
    {
        try {
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;

            $nominee_profile = Profile::find($profile->nominee_id);
            $grantor_profile = Profile::find($profile->grantor_id);


            $info = array(
                'name' => !empty($nominee_profile) ? $nominee_profile->name : null,
                'mobile' => !empty($nominee_profile) ? $nominee_profile->mobile : null,
                'nominee_relation' => !empty($nominee_profile) ? $profile->nominee_relation : null,
                'picture' => !empty($nominee_profile) ? $nominee_profile->pro_pic : null,

                'nid_front_image' => !empty($nominee_profile) ? $nominee_profile->nid_front_image : null,
                'nid_back_image' => !empty($nominee_profile) ? $nominee_profile->nid_back_image : null,
                'grantor' => [
                    'name' => !empty($grantor_profile) ? $grantor_profile->name : null,
                    'mobile' => !empty($grantor_profile) ? $grantor_profile->mobile : null,
                    'grantor_relation' => !empty($grantor_profile) ? $profile->grantor_relation : null,
                    'picture' => !empty($grantor_profile) ? $grantor_profile->pro_pic : null,

                    'nid_front_image' => !empty($grantor_profile) ? $grantor_profile->nid_front_image : null,
                    'nid_back_image' => !empty($grantor_profile) ? $grantor_profile->nid_back_image : null,
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateNomineeInformation($partner, Request $request)
    {
        try {
            $manager_resource = $request->manager_resource;
            $manager_resource_profile = $manager_resource->profile;
            #dd($manager_resource_profile);

            $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
            if ($profile) {
                $data = [
                    'nominee_id' => $profile->id,
                    'nominee_relation' => $request->nominee_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
            } else {
                $profile = $this->createProfile($request);
                $data = [
                    'nominee_id' => $profile->id,
                    'nominee_relation' => $request->nominee_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
            }

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateGrantorInformation($partner, Request $request)
    {
        try {
            $manager_resource = $request->manager_resource;
            $manager_resource_profile = $manager_resource->profile;

            $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
            if ($profile) {
                $data = [
                    'grantor_id' => $profile->id,
                    'grantor_relation' => $request->granter_relation
                ];

                $manager_resource_profile->update($this->withBothModificationFields($data));
            } else {
                $profile = $this->createProfile($request);
                $data = [
                    'grantor_id' => $profile->id,
                    'grantor_relation' => $request->granter_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
            }

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function createProfile(Request $request)
    {
        $profile = new Profile();
        $profile->remember_token = str_random(255);
        $profile->name = $request->name;
        $profile->mobile = formatMobile($request->mobile);
        $this->withCreateModificationField($profile);
        $profile->save();
        return $profile;
    }

    public function getDocuments($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations = $partner->bankInformations;

            $nominee_profile = Profile::find($profile->nominee_id);

            $info = array(
                'picture' => $profile->pro_pic,
                'nid_front_image' => $profile->nid_front_image,
                'nid_back_image' => $profile->nid_back_image,
                'nominee_document' => [
                    'picture' => !empty($nominee_profile) ? $nominee_profile->pro_pic : null,
                    'nid_front_image' => !empty($nominee_profile) ? $nominee_profile->nid_front_image : null,
                    'nid_back_image' => !empty($nominee_profile) ? $nominee_profile->nid_back_image : null,
                ],
                'business_document' => [
                    'tin_certificate' => $profile->tin_certificate,
                    'trade_license_attachment' => $basic_informations->trade_license_attachment,
                    'statement' => $bank_informations->statement
                ],

            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateProfilePictures($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'picture' => 'required|mimes:jpeg,png'
            ]);
            $manager_resource = $request->manager_resource;
            $profile = $manager_resource->profile;
            $image_for = $request->image_for;
            $nominee = (bool)$request->nominee;
            $grantor = (bool)$request->grantor;

            if ($nominee) {
                if (!$profile->nominee_id) {
                    return api_response($request, null, 401, ['message' => 'Create Nominee First']);
                } else {
                    $profile = Profile::find($profile->nominee_id);
                }
            }
            if ($grantor) {
                if (!$profile->grantor_id) {
                    return api_response($request, null, 401, ['message' => 'Create Grantor First']);
                } else {
                    $profile = Profile::find($profile->grantor_id);
                }
            }

            $photo = $request->file('picture');
            if (basename($profile->image_for) != 'default.jpg') {
                $filename = substr($profile->{$image_for}, strlen(config('sheba.s3_url')));
                $this->deleteOldImage($filename);
            }

            $picture_link = $this->fileRepository->uploadToCDN($this->makePicName($profile, $photo, $image_for), $photo, 'images/profile/');

            if ($picture_link != false) {
                $data[$image_for] = $picture_link;
                $profile->update($this->withUpdateModificationField($data));

                return api_response($request, $profile, 200, ['picture' => $profile->{$image_for}]);
            } else {
                return api_response($request, null, 500);
            }
        } catch
        (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

    private function makePicName($profile, $photo, $image_for = 'profile')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $profile->id . '.' . $photo->extension();
    }

}
