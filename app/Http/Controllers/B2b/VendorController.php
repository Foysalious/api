<?php namespace App\Http\Controllers\B2b;

use App\Helper\BangladeshiMobileValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Excel;
use Intervention\Image\Image;
use Sheba\Business\Vendor\BulkUploadExcel;
use Sheba\Business\Vendor\BulkUploadExcelError;
use Sheba\Business\Vendor\CreateRequest;
use Sheba\Business\Vendor\Creator;
use Sheba\Business\Vendor\Updater;
use Sheba\Business\Vendor\UpdateRequest;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use Throwable;

class VendorController extends Controller
{
    use ModificationFields;

    private $profileRepository;

    /**
     * VendorController constructor.
     * @param ProfileRepository $profile_repo
     */
    public function __construct(ProfileRepository $profile_repo)
    {
        $this->profileRepository = $profile_repo;
    }

    /**
     * @param Request $request
     * @param CreateRequest $create_request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, CreateRequest $create_request, Creator $creator)
    {
        $validation_data = [
            'vendor_name' => 'required',
            'vendor_image' => 'sometimes|required|image|max:800|mimes:jpeg,png',
            'resource_name' => 'required',
            'resource_mobile' => 'required|string|mobile:bd'
        ];

        if ($request->trade_license_document && $this->isFile($request->trade_license_document)) $validation_data['trade_license_document'] = 'sometimes|required|image|max:800|mimes:jpeg,png';
        if ($request->vat_registration_document && $this->isFile($request->vat_registration_document)) $validation_data['vat_registration_document'] = 'sometimes|required|image|max:800|mimes:jpeg,png';
        if ($request->resource_nid_front && $this->isFile($request->resource_nid_front)) $validation_data['resource_nid_front'] = 'sometimes|required|image|max:800|mimes:jpeg,png';
        if ($request->resource_nid_back && $this->isFile($request->resource_nid_back)) $validation_data['resource_nid_back'] = 'sometimes|required|image|max:800|mimes:jpeg,png';

        $this->validate($request, $validation_data, [
                'vendor_name.required' => 'Company name can not be empty.',
                'resource_name.required' => 'SP name can not be empty.',
                'resource_mobile.required' => 'Vendor phone number can not be empty.',
            ]
        );

        $business = $request->business;
        $member = $request->manager_member;
        $this->setModifier($member);

        /** @var CreateRequest $request */
        $create_request = $create_request->setBusiness($business)
            ->setVendorName($request->vendor_name)
            ->setVendorEmail($request->vendor_email)
            ->setVendorImage($request->vendor_image)
            ->setVendorAddress($request->vendor_address)
            ->setVendorMasterCategories($request->vendor_master_categories)
            ->setTradeLicenseNumber($request->trade_license_number)
            ->setTradeLicenseDocument($request->trade_license_document)
            ->setVatRegistrationNumber($request->vat_registration_number)
            ->setVatRegistrationDocument($request->vat_registration_document)
            ->setResourceName($request->resource_name)
            ->setResourceMobile($request->resource_mobile)
            ->setResourceNidNumber($request->resource_nid_number)
            ->setResourceNidFront($request->resource_nid_front)
            ->setResourceNidback($request->resource_nid_back)
            ->setIsActiveForB2b($request->is_active_for_b2b);

        if ($create_request->hasError())
            return response()->json(['code' => $create_request->getErrorCode(), 'message' => $create_request->getErrorMessage()]);

        $creator->setVendorCreateRequest($create_request);
        if ($error = $creator->hasError())
            return api_response($request, null, 400, $error);

        $vendor = $creator->create();

        return api_response($request, null, 200, ['vendor_id' => $vendor->id, 'message' => 'Vendor Created Successfully']);
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFile($file)
    {
        if ($file instanceof Image || $file instanceof UploadedFile) return true;
        return false;
    }

    /**
     * @param Request $request
     * @param CreateRequest $create_request
     * @param Creator $creator
     * @param BulkUploadExcelError $bulk_upload_excel_error
     * @return JsonResponse
     */
    public function bulkStore(Request $request, CreateRequest $create_request, Creator $creator, BulkUploadExcelError $bulk_upload_excel_error)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);
            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();
            if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

            $admin_member = $request->manager_member;
            $business = $request->business;
            $this->setModifier($admin_member);

            $file = Excel::selectSheets(BulkUploadExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(BulkUploadExcel::SHEET)->load($file_path)->get();

            $data = $data->filter(function ($row) {
                return ($row->vendor_name && $row->contact_person_name && $row->contact_person_mobile);
            });

            $total = $data->count();
            $total_count = 0;
            $error_count = 0;
            $vendor_name = BulkUploadExcel::VENDOR_NAME_COLUMN_TITLE;
            $phone_number = BulkUploadExcel::PHONE_NUMBER_COLUMN_TITLE;
            $contact_person_name = BulkUploadExcel::CONTACT_PERSON_NAME_COLUMN_TITLE;
            $contact_person_mobile = BulkUploadExcel::CONTACT_PERSON_MOBILE_COLUMN_TITLE;
            $address = BulkUploadExcel::ADDRESS_COLUMN_TITLE;
            $email = BulkUploadExcel::EMAIL_COLUMN_TITLE;
            $trade_license = BulkUploadExcel::TRADE_LICENSE_NUMBER_COLUMN_TITLE;
            $vat_registration = BulkUploadExcel::VAT_REGISTRATION_NUMBER_COLUMN_TITLE;

            $excel_error = null;
            $halt_top_up = false;
            $data->each(function ($value, $key) use ($business, $file_path, $total, $excel_error, &$halt_top_up, $contact_person_mobile, $bulk_upload_excel_error) {
                if (!$this->isMobileNumberValid($value->$contact_person_mobile)) {
                    $halt_top_up = true;
                    $excel_error = 'Mobile number Invalid';
                } elseif ($this->isMobileNumberAlreadyExist($value->$contact_person_mobile)) {
                    $halt_top_up = true;
                    $excel_error = 'This mobile number already exist';
                } else {
                    $excel_error = null;
                }
                $bulk_upload_excel_error->setAgent($business)->setFile($file_path)->setRow($key + 2)->setTotalRow($total)->updateExcel($excel_error);
            });
            if ($halt_top_up) {
                $excel_data_format_errors = $bulk_upload_excel_error->takeCompletedAction();
                return api_response($request, null, 420, ['message' => 'Check The Excel Properly', 'excel_errors' => $excel_data_format_errors]);
            }

            $data->each(function ($value) use (
                $create_request, $creator, $admin_member, &$error_count, &$total_count, $business,
                $vendor_name, $phone_number, $contact_person_name, $contact_person_mobile, $address, $email,
                $trade_license, $vat_registration
            ) {
                $total_count++;
                if (!($value->$vendor_name && $value->$phone_number && $value->$trade_license && $value->$vat_registration)) {
                    $error_count++;
                    return;
                }
                /** @var CreateRequest $request */
                $create_request = $create_request->setBusiness($business)
                    ->setVendorName($value->$vendor_name)
                    ->setVendorMobile($value->$phone_number)
                    ->setVendorEmail($value->$email)
                    ->setVendorAddress($value->$address)
                    ->setResourceName($value->$contact_person_name)
                    ->setResourceMobile($value->$contact_person_mobile)
                    ->setTradeLicenseNumber($value->$trade_license)
                    ->setVatRegistrationNumber($value->$vat_registration);

                $creator->setVendorCreateRequest($create_request);
                if ($error = $creator->hasError()) {
                    $error_count++;
                } else {
                    $creator->create();
                }
            });

            $response_message = ($total_count - $error_count) . " Vendor's Created Successfully, Failed {$error_count} vendor's";
            return api_response($request, null, 200, ['message' => $response_message]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $mobile
     * @return bool
     */
    private function isMobileNumberValid($mobile)
    {
        return BangladeshiMobileValidator::validate(BDMobileFormatter::format($mobile));
    }

    /**
     * @param $mobile
     * @return bool
     */
    private function isMobileNumberAlreadyExist($mobile)
    {
        $profile = $this->profileRepository->checkExistingMobile($mobile);
        if ($profile) return true;
        return false;
    }


    public function activeInactive($business, $vendor, Request $request, UpdateRequest $update_request, Updater $updater, PartnerRepositoryInterface $partner_repository)
    {
        try {
            $this->validate($request, [
                'active' => 'required'
            ]);
            $vendor = $partner_repository->find($vendor);
            $business = $request->business;
            $manager_member = $request->manager_member;
            $this->setModifier($manager_member);

            /** @var UpdateRequest $request */
            $update_request->setVendor($vendor)->setIsActiveForB2B($request->active);
            $updater->setVendorUpdateRequest($update_request)->activeInactiveForB2b();

            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
