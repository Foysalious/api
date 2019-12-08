<?php namespace App\Http\Controllers;

use App\Models\PartnerBankInformation;
use App\Models\PartnerBankLoan;
use App\Models\Profile;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;

class SpLoanController extends Controller
{
    use CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;

    public function __construct(FileRepository $file_repository)
    {
        $this->fileRepository = $file_repository;
    }

    public function getHomepage($partner, Request $request)
    {
        try {
            $partner   = $request->partner;
            $bank_name = null;
            if (!$partner->loan->isEmpty()) {
                $last      = $partner->loan->last();

                $bank_name = $last->bank_name ?: (($last->bank) ? $last->bank->name : constants('AVAILABLE_BANK_FOR_LOAN')['BRAC']['name']);
            }
            $homepage = [
                'running_application' => [
                    'bank_name'   => !$partner->loan->isEmpty() ? $partner->loan->last()->bank_name : null,
                    'logo'        => !$partner->loan->isEmpty() ? $bank_name ? constants('AVAILABLE_BANK_FOR_LOAN')[$bank_name]['logo'] : null : null,
                    'loan_amount' => !$partner->loan->isEmpty() ? $partner->loan->last()->loan_amount : null,
                    'status'      => !$partner->loan->isEmpty() ? $partner->loan->last()->status : null,
                    'duration'    => !$partner->loan->isEmpty() ? $partner->loan->last()->duration : null
                ],
                'big_banner'          => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v3_1440_628.jpg',
                'banner'              => config('sheba.s3_url') . 'images/offers_images/banners/loan_banner_v3_720_324.jpg',
                'title'               => 'হাতের নাগালে ব্যাংক লোন -',
                'list'                => [
                    'সহজ শর্তে লোন নিন',
                    'সেবার মাধ্যমে লোন প্রসেসিং',
                    'প্রয়োজনীয় তথ্য দিয়ে সুবিধা মত লোন গ্রহন করুন'
                ],
            ];
            return api_response($request, $homepage, 200, ['homepage' => $homepage]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBankInterest($partner, Request $request)
    {
        try {
            $bank_lists = array_values(constants('AVAILABLE_BANK_FOR_LOAN'));
            return api_response($request, $bank_lists, 200, ['bank_lists' => $bank_lists]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($partner, Request $request, PartnerBankLoan $loan)
    {
        try {
            $this->validate($request, [
                'bank_name'           => 'required|string',
                'loan_amount'         => 'required|numeric',
                'duration'            => 'required|integer',
                'monthly_installment' => 'required|numeric',
                'status'              => 'required|string',
            ]);
            $partner      = $request->partner;
            $data         = [
                'partner_id'                 => $partner->id,
                'bank_name'                  => $request->bank_name,
                'loan_amount'                => $request->loan_amount,
                'status'                     => $request->status,
                'duration'                   => $request->duration,
                'monthly_installment'        => $request->monthly_installment,
                'final_information_for_loan' => json_encode([$this->finalInformationForLoan($partner, $request)])
            ];
            $partner_loan = PartnerBankLoan::where('partner_id', $partner->id)->get()->last();
            if ($partner_loan && in_array($partner_loan->status, [
                    'approved',
                    'considerable'
                ])) {
                return api_response($request, null, 403, ['message' => "You already applied for loan"]);
            } else {
                $loan->create($this->withCreateModificationField($data));
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

    private function finalInformationForLoan($partner, Request $request)
    {
        $manager_resource                = $request->manager_resource;
        $profile                         = $manager_resource->profile;
        $basic_informations              = $partner->basicInformations;
        $bank_informations               = $partner->bankInformations;
        $business_additional_information = $partner->businessAdditionalInformation();
        $sales_information               = $partner->salesInformation();
        #$nominee_profile = Profile::find($profile->nominee_id);
        $grantor_profile = Profile::find($profile->grantor_id);
        return [
            'personal_info'        => [
                'name'              => $profile->name,
                'mobile'            => $profile->mobile,
                'gender'            => $profile->gender,
                'picture'           => $profile->pro_pic,
                'birthday'          => $profile->dob,
                'present_address'   => $profile->address,
                'permanent_address' => $profile->permanent_address,
                'father_name'       => $manager_resource->father_name,
                'spouse_name'       => $manager_resource->spouse_name,
                'occupation'        => $profile->occupation,
                'expenses'          => [
                    'monthly_living_cost'     => $profile->monthly_living_cost,
                    'total_asset_amount'      => $profile->total_asset_amount,
                    #'monthly_loan_installment_amount' => $profile->monthly_loan_installment_amount,
                    'utility_bill_attachment' => $profile->utility_bill_attachment
                ]
            ],
            'business_info'        => [
                'business_name'                    => $partner->name,
                'business_type'                    => $partner->business_type,
                'location'                         => $partner->address,
                'establishment_year'               => $basic_informations->establishment_year,
                'full_time_employee'               => $partner->full_time_employee,
                #'part_time_employee' => $partner->part_time_employee,
                'business_additional_information'  => [
                    #'product_price' => isset($business_additional_information->product_price) ? $business_additional_information->product_price : null,
                    'employee_salary' => isset($business_additional_information->employee_salary) ? $business_additional_information->employee_salary : null,
                    'office_rent'     => isset($business_additional_information->office_rent) ? $business_additional_information->office_rent : null,
                    #'utility_bills' => isset($business_additional_information->utility_bills) ? $business_additional_information->utility_bills : null,
                    #'marketing_cost' => isset($business_additional_information->marketing_cost) ? $business_additional_information->marketing_cost : null,
                    #'other_costs' => isset($business_additional_information->other_costs) ? $business_additional_information->other_costs : null
                ],
                'last_six_month_sales_information' => [
                    'avg_sell' => isset($sales_information->last_six_month_avg_sell) ? $sales_information->last_six_month_avg_sell : null,
                    'min_sell' => isset($sales_information->last_six_month_min_sell) ? $sales_information->last_six_month_min_sell : null,
                    'max_sell' => isset($sales_information->last_six_month_max_sell) ? $sales_information->last_six_month_max_sell : null,
                ]
            ],
            'finance_info'         => [
                'account_holder_name' => !empty($bank_informations) ? $bank_informations->acc_name : null,
                'account_no'          => !empty($bank_informations) ? $bank_informations->acc_no : null,
                'bank_name'           => !empty($bank_informations) ? $bank_informations->bank_name : null,
                'brunch'              => !empty($bank_informations) ? $bank_informations->branch_name : null,
                'acc_type'            => !empty($bank_informations) ? $bank_informations->acc_type : null,
                'bkash'               => [
                    'bkash_no'           => $partner->bkash_no,
                    'bkash_account_type' => $partner->bkash_account_type,
                ]
            ],
            'nominee_grantor_info' => [
                /*'name' => !empty($nominee_profile) ? $nominee_profile->name : null,
                'mobile' => !empty($nominee_profile) ? $nominee_profile->mobile : null,
                'nominee_relation' => !empty($nominee_profile) ? $profile->nominee_relation : null,*/
                'grantor' => [
                    'name'             => !empty($grantor_profile) ? $grantor_profile->name : null,
                    'mobile'           => !empty($grantor_profile) ? $grantor_profile->mobile : null,
                    'grantor_relation' => !empty($grantor_profile) ? $profile->grantor_relation : null,
                ]
            ],
            'documents'            => [
                'picture'           => $profile->pro_pic,
                'nid_image'         => $manager_resource->nid_image,
                'nid_image_front'   => $profile->nid_image_front,
                'nid_image_back'    => $profile->nid_image_back,
                /*'nominee_document' => [
                    'picture' => !empty($nominee_profile) ? $nominee_profile->pro_pic : null,
                    'nid_image_front' => !empty($nominee_profile) ? $nominee_profile->nid_image_front : null,
                    'nid_image_back' => !empty($nominee_profile) ? $nominee_profile->nid_image_back : null,
                ],*/
                'grantor_document'  => [
                    'picture'         => !empty($grantor_profile) ? $grantor_profile->pro_pic : null,
                    'nid_image_front' => !empty($grantor_profile) ? $grantor_profile->nid_image_front : null,
                    'nid_image_back'  => !empty($grantor_profile) ? $grantor_profile->nid_image_back : null,
                ],
                'business_document' => [
                    'tin_certificate'          => $profile->tin_certificate,
                    'trade_license_attachment' => $basic_informations->trade_license_attachment,
                    #'statement' => !empty($bank_informations) ? $bank_informations->statement : null
                ],
            ]
        ];
    }

    public function getPersonalInformation($partner, Request $request)
    {
        try {
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $profile          = $manager_resource->profile;
            $info             = array(
                'name'              => $profile->name,
                'mobile'            => $profile->mobile,
                'gender'            => $profile->gender,
                'genders'           => constants('GENDER'),
                'picture'           => $profile->pro_pic,
                'birthday'          => $profile->dob,
                'present_address'   => $profile->address,
                'permanent_address' => $profile->permanent_address,
                'father_name'       => $manager_resource->father_name,
                'spouse_name'       => $manager_resource->spouse_name,
                'occupation_lists'  => constants('SUGGESTED_OCCUPATION'),
                'occupation'        => $profile->occupation,
                'expenses'          => [
                    'monthly_living_cost'     => (int)$profile->monthly_living_cost ? $profile->monthly_living_cost : null,
                    'total_asset_amount'      => (int)$profile->total_asset_amount ? $profile->total_asset_amount : null,
                    #'monthly_loan_installment_amount' => (int)$profile->monthly_loan_installment_amount ? $profile->monthly_loan_installment_amount : null,
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
            $this->validate($request, [
                'gender'              => 'required|string|in:Male,Female,Other',
                'dob'                 => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'address'             => 'required|string',
                'permanent_address'   => 'required|string',
                #'father_name' => 'required_without:spouse_name',
                #'spouse_name' => 'required_without:father_name',
                'occupation'          => 'string',
                'monthly_living_cost' => 'numeric',
                'total_asset_amount'  => 'numeric',
                #'monthly_loan_installment_amount' => 'numeric'
            ]);
            $manager_resource = $request->manager_resource;
            $profile          = $manager_resource->profile;
            $profile_data     = array(
                'gender'              => $request->gender,
                'dob'                 => $request->dob,
                'address'             => $request->address,
                'permanent_address'   => $request->permanent_address,
                'occupation'          => $request->occupation,
                'monthly_living_cost' => $request->monthly_living_cost,
                'total_asset_amount'  => $request->total_asset_amount,
                #'monthly_loan_installment_amount' => $request->monthly_loan_installment_amount,
            );
            $resource_data    = [
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
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBusinessInformation($partner, Request $request)
    {
        try {
            $partner                         = $request->partner;
            $manager_resource                = $request->manager_resource;
            $profile                         = $manager_resource->profile;
            $basic_informations              = $partner->basicInformations;
            $bank_informations               = $partner->bankInformations;
            $business_additional_information = $partner->businessAdditionalInformation();
            $sales_information               = $partner->salesInformation();
            $info                            = array(
                'business_name'                    => $partner->name,
                'business_type'                    => $partner->business_type,
                'location'                         => $partner->address,
                'establishment_year'               => $basic_informations->establishment_year,
                'full_time_employee'               => !empty($partner->full_time_employee) ? $partner->full_time_employee : null,
                #'part_time_employee' => !empty($partner->part_time_employee) ? $partner->part_time_employee : null,
                'business_additional_information'  => [
                    #'product_price' => isset($business_additional_information->product_price) ? $business_additional_information->product_price : null,
                    'employee_salary' => isset($business_additional_information->employee_salary) ? $business_additional_information->employee_salary : null,
                    'office_rent'     => isset($business_additional_information->office_rent) ? $business_additional_information->office_rent : null,
                    #'utility_bills' => isset($business_additional_information->utility_bills) ? $business_additional_information->utility_bills : null,
                    #'marketing_cost' => isset($business_additional_information->marketing_cost) ? $business_additional_information->marketing_cost : null,
                    #'other_costs' => isset($business_additional_information->other_costs) ? $business_additional_information->other_costs : null
                ],
                'last_six_month_sales_information' => [
                    'avg_sell' => isset($sales_information->last_six_month_avg_sell) ? $sales_information->last_six_month_avg_sell : null,
                    'min_sell' => isset($sales_information->last_six_month_min_sell) ? $sales_information->last_six_month_min_sell : null,
                    'max_sell' => isset($sales_information->last_six_month_max_sell) ? $sales_information->last_six_month_max_sell : null,
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
            $this->validate($request, [
                'business_type'      => 'string',
                'location'           => 'required|string',
                'establishment_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'full_time_employee' => 'numeric',
                #'part_time_employee' => 'numeric',
                #'sales_information' => 'required',
                #'business_additional_information' => 'required'
            ]);
            $partner            = $request->partner;
            $basic_informations = $partner->basicInformations;
            $partner_data       = [
                'business_type'                   => $request->business_type,
                'address'                         => $request->location,
                'full_time_employee'              => $request->full_time_employee,
                #'part_time_employee' => $request->part_time_employee,
                'sales_information'               => $request->sales_information,
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
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getFinanceInformation($partner, Request $request)
    {
        try {
            $partner            = $request->partner;
            $manager_resource   = $request->manager_resource;
            $profile            = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations  = $partner->bankInformations;
            $info               = [
                'account_holder_name' => !empty($bank_informations) ? $bank_informations->acc_name : null,
                'account_no'          => !empty($bank_informations) ? $bank_informations->acc_no : null,
                'bank_name'           => !empty($bank_informations) ? $bank_informations->bank_name : null,
                'brunch'              => !empty($bank_informations) ? $bank_informations->branch_name : null,
                'acc_type'            => !empty($bank_informations) ? $bank_informations->acc_type : null,
                'acc_types'           => constants('BANK_ACCOUNT_TYPE'),
                'bkash'               => [
                    'bkash_no'            => $partner->bkash_no,
                    'bkash_account_type'  => $partner->bkash_account_type,
                    'bkash_account_types' => constants('BKASH_ACCOUNT_TYPE')
                ]
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateFinanceInformation($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'acc_name'           => 'required|string',
                'acc_no'             => 'required|string',
                'bank_name'          => 'required|string',
                'branch_name'        => 'required|string',
                'acc_type'           => 'string|in:savings,current',
                'bkash_no'           => 'string|mobile:bd',
                'bkash_account_type' => 'string|in:personal,agent,merchant'
            ]);
            $partner           = $request->partner;
            $bank_informations = $partner->bankInformations;
            $bank_data         = [
                'partner_id'  => $partner->id,
                'acc_name'    => $request->acc_name,
                'acc_no'      => $request->acc_no,
                'bank_name'   => $request->bank_name,
                'branch_name' => $request->branch_name,
                'acc_type'    => $request->acc_type
            ];
            $partner_data      = [
                'bkash_no'           => !empty($request->bkash_no) ? formatMobile($request->bkash_no) : null,
                'bkash_account_type' => $request->bkash_account_type
            ];
            if ($bank_informations) {
                $bank_informations->update($this->withBothModificationFields($bank_data));
            } else {
                PartnerBankInformation::create($this->withCreateModificationField($bank_data));
            }
            $partner->update($this->withBothModificationFields($partner_data));
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNomineeInformation($partner, Request $request)
    {
        try {
            $manager_resource = $request->manager_resource;
            $profile          = $manager_resource->profile;
            #$nominee_profile = Profile::find($profile->nominee_id);
            $grantor_profile = Profile::find($profile->grantor_id);
            $info            = array(
                /*'name' => !empty($nominee_profile) ? $nominee_profile->name : null,
                'mobile' => !empty($nominee_profile) ? $nominee_profile->mobile : null,
                'nominee_relation' => !empty($nominee_profile) ? $profile->nominee_relation : null,

                'picture' => !empty($nominee_profile) ? $nominee_profile->pro_pic : null,
                'nid_front_image' => !empty($nominee_profile) ? $nominee_profile->nid_image_front : null,
                'nid_back_image' => !empty($nominee_profile) ? $nominee_profile->nid_image_back : null,*/
                'grantor' => [
                    'name'             => !empty($grantor_profile) ? $grantor_profile->name : null,
                    'mobile'           => !empty($grantor_profile) ? $grantor_profile->mobile : null,
                    'grantor_relation' => !empty($grantor_profile) ? $profile->grantor_relation : null,
                    'picture'          => !empty($grantor_profile) ? $grantor_profile->pro_pic : null,
                    'nid_front_image'  => !empty($grantor_profile) ? $grantor_profile->nid_image_front : null,
                    'nid_back_image'   => !empty($grantor_profile) ? $grantor_profile->nid_image_back : null,
                ]
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateNomineeGrantorInformation($partner, Request $request)
    {
        try {
            $this->validate($request, [
                /*'nominee_name' => 'required|string',
                'nominee_mobile' => 'required|string|mobile:bd',
                'nominee_relation' => 'required|string',*/
                'grantor_name'     => 'required|string',
                'grantor_mobile'   => 'required|string|mobile:bd',
                'grantor_relation' => 'required|string'
            ]);
            $partner                  = $request->partner;
            $manager_resource         = $request->manager_resource;
            $manager_resource_profile = $manager_resource->profile;
            /*$nominee_profile = Profile::where('mobile', formatMobile($request->nominee_mobile))->first();
            if ($nominee_profile) {
                $data = [
                    'nominee_id' => $nominee_profile->id,
                    'nominee_relation' => $request->nominee_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
            } else {
                $nominee_profile = $this->createNomineeProfile($partner, $request);

                $data = [
                    'nominee_id' => $nominee_profile->id,
                    'nominee_relation' => $request->nominee_relation
                ];

                $manager_resource_profile->update($this->withBothModificationFields($data));
            }*/
            $grantor_profile = Profile::where('mobile', formatMobile($request->grantor_mobile))->first();
            if ($grantor_profile) {
                $data = [
                    'grantor_id'       => $grantor_profile->id,
                    'grantor_relation' => $request->grantor_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
            } else {
                $grantor_profile = $this->createGrantorProfile($partner, $request);
                $data            = [
                    'grantor_id'       => $grantor_profile->id,
                    'grantor_relation' => $request->grantor_relation
                ];
                $manager_resource_profile->update($this->withBothModificationFields($data));
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

    private function createGrantorProfile($partner, Request $request)
    {
        $this->setModifier($partner);
        $profile                 = new Profile();
        $profile->remember_token = str_random(255);
        $profile->name           = $request->grantor_name;
        $profile->mobile         = !empty($request->grantor_mobile) ? formatMobile($request->grantor_mobile) : null;
        $this->withCreateModificationField($profile);
        $profile->save();
        return $profile;
    }

    public function getDocuments($partner, Request $request)
    {
        try {
            $partner            = $request->partner;
            $manager_resource   = $request->manager_resource;
            $profile            = $manager_resource->profile;
            $basic_informations = $partner->basicInformations;
            $bank_informations  = $partner->bankInformations;
            #$nominee_profile = Profile::find($profile->nominee_id);
            $grantor_profile = Profile::find($profile->grantor_id);
            $info            = array(
                'picture'           => $profile->pro_pic,
                'nid_image'         => $manager_resource->nid_image,
                'is_verified'       => $manager_resource->is_verified,
                'nid_image_front'   => $profile->nid_image_front,
                'nid_image_back'    => $profile->nid_image_back,
                /*'nominee_document' => [
                    'picture' => !empty($nominee_profile) ? $nominee_profile->pro_pic : null,
                    'nid_front_image' => !empty($nominee_profile) ? $nominee_profile->nid_image_front : null,
                    'nid_back_image' => !empty($nominee_profile) ? $nominee_profile->nid_image_back : null,
                ],*/
                'grantor_document'  => [
                    'picture'         => !empty($grantor_profile) ? $grantor_profile->pro_pic : null,
                    'nid_front_image' => !empty($grantor_profile) ? $grantor_profile->nid_image_front : null,
                    'nid_back_image'  => !empty($grantor_profile) ? $grantor_profile->nid_image_back : null,
                ],
                'business_document' => [
                    'tin_certificate'          => $profile->tin_certificate,
                    'trade_license_attachment' => $basic_informations->trade_license_attachment,
                    #'statement' => !empty($bank_informations) ? $bank_informations->statement : null
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
            $profile          = $manager_resource->profile;
            $image_for        = $request->image_for;
            $nominee          = (bool)$request->nominee;
            $grantor          = (bool)$request->grantor;
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
            $picture_link = $this->fileRepository->uploadToCDN($this->makePicName($profile, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
            if ($picture_link != false) {
                $data[$image_for] = $picture_link;
                $profile->update($this->withUpdateModificationField($data));
                return api_response($request, $profile, 200, ['picture' => $profile->{$image_for}]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
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

    public function updateBankStatement($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'picture' => 'required|mimes:jpeg,png'
            ]);
            $partner           = $request->partner;
            $bank_informations = $partner->bankInformations;
            if (!$bank_informations)
                $bank_informations = $this->createBankInformation($partner);
            $file_name = $request->picture;
            if ($bank_informations->statement != getBankStatementDefaultImage()) {
                $old_statement = substr($bank_informations->statement, strlen(config('s3.url')));
                $this->deleteImageFromCDN($old_statement);
            }
            $bank_statement = $this->saveBankStatement($file_name);
            if ($bank_statement != false) {
                $data['statement'] = $bank_statement;
                $bank_informations->update($this->withUpdateModificationField($data));
                return api_response($request, $bank_statement, 200, ['picture' => $bank_informations->statement]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createBankInformation($partner)
    {
        $this->setModifier($partner);
        $bank_information              = new PartnerBankInformation();
        $bank_information->partner_id  = $partner->id;
        $bank_information->is_verified = $partner->status == 'Verified' ? 1 : 0;
        $this->withCreateModificationField($bank_information);
        $bank_information->save();
        return $bank_information;
    }

    private function saveBankStatement($image_file)
    {
        list($bank_statement, $statement_filename) = $this->makeBankStatement($image_file, 'bank_statement');
        return $this->saveImageToCDN($bank_statement, getBankStatementImagesFolder(), $statement_filename);
    }

    public function updateTradeLicense($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'picture' => 'required|mimes:jpeg,png'
            ]);
            $partner            = $request->partner;
            $basic_informations = $partner->basicInformations;
            $file_name          = $request->picture;
            if ($basic_informations->trade_license_attachment != getTradeLicenseDefaultImage()) {
                $old_statement = substr($basic_informations->trade_license_attachment, strlen(config('s3.url')));
                $this->deleteImageFromCDN($old_statement);
            }
            $trade_license = $this->saveTradeLicense($file_name);
            if ($trade_license != false) {
                $data['trade_license_attachment'] = $trade_license;
                $basic_informations->update($this->withUpdateModificationField($data));
                return api_response($request, $trade_license, 200, ['picture' => $basic_informations->trade_license_attachment]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    private function saveTradeLicense($image_file)
    {
        list($trade_license, $trade_license_filename) = $this->makeTradeLicense($image_file, 'trade_license_attachment');
        return $this->saveImageToCDN($trade_license, getTradeLicenceImagesFolder(), $trade_license_filename);
    }

    private function createNomineeProfile($partner, Request $request)
    {
        $this->setModifier($partner);
        $profile                 = new Profile();
        $profile->remember_token = str_random(255);
        $profile->name           = $request->nominee_name;
        $profile->mobile         = !empty($request->nominee_mobile) ? formatMobile($request->nominee_mobile) : null;
        $this->withCreateModificationField($profile);
        $profile->save();
        return $profile;
    }

}
