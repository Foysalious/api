<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\CoWorker\BulkBkashNumber\BulkBkashNumberExcelUploadError;
use App\Sheba\Business\CoWorker\BulkBkashNumber\BulkBkashNumberExcel;
use App\Transformers\Business\CoWorkerBkashNumberReportTransformer;
use App\Sheba\Business\CoWorker\BulkBkashNumber\BkashNumberExcel;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Sheba\Business\BusinessMemberBkashAccount\Requester as CoWorkerBkashAccountRequester;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use App\Models\Business;
use App\Models\Member;

class CoWorkerBkashController extends Controller
{
    use ModificationFields;

    /** @var ProfileRepositoryInterface $profileRepo */
    private $profileRepo;
    /** @var CoWorkerBkashAccountRequester $coWorkerBkashAccRequester */
    private $coWorkerBkashAccRequester;

    public function __construct(ProfileRepositoryInterface $profile_repo, CoWorkerBkashAccountRequester $co_worker_bkash_acc_requester)
    {
        $this->profileRepo = $profile_repo;
        $this->coWorkerBkashAccRequester = $co_worker_bkash_acc_requester;
    }

    public function bulkGrossSalaryUpload($business, Request $request, BulkBkashNumberExcelUploadError $bulk_bkash_excel_upload_error)
    {
        $this->validate($request, ['file' => 'required|file']);
        $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
        $extension = $request->file('file')->getClientOriginalExtension();
        if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);

        $file = Excel::selectSheets(BkashNumberExcel::SHEET)->load($request->file)->save();
        $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
        $data = Excel::selectSheets(BkashNumberExcel::SHEET)->load($file_path)->get();

        $total = $data->count();
        $employee_email = BkashNumberExcel::USERS_MAIL_COLUMN_TITLE;
        $bkash_number = BkashNumberExcel::USERS_BKASH_COLUMN_TITLE;

        $excel_error = null;
        $halt_execution = false;
        $bulk_bkash_excel_upload_error->setBusiness($business)->setFile($file_path);

        $data->each(function ($value, $key) use ($business, $file_path, $total, $employee_email, $bkash_number, $excel_error, &$halt_execution, $bulk_bkash_excel_upload_error) {
            $profile = $this->profileRepo->checkExistingEmail($value->$employee_email);

            if (!$value->$employee_email || !$value->$bkash_number) {
                $halt_execution = true;
                $excel_error = 'Email or bkash number cannot be empty';
            } elseif (!isEmailValid($value->$employee_email)) {
                $halt_execution = true;
                $excel_error = 'Email is invalid';
            } elseif (!$profile) {
                $halt_execution = true;
                $excel_error = 'Profile not found';
            } elseif (!$profile->member) {
                $halt_execution = true;
                $excel_error = 'Member not found';
            } elseif (!$profile->member->activeBusinessMember->first()) {
                $halt_execution = true;
                $excel_error = 'Business Member not found';
            } else {
                $excel_error = null;
            }
            $bulk_bkash_excel_upload_error->setRow($key + 2)->setTotalRow($total)->updateExcel($excel_error);
        });

        if ($halt_execution) {
            $excel_data_format_errors = $bulk_bkash_excel_upload_error->takeCompletedAction();
            return api_response($request, null, 420, ['message' => 'Check The Excel Properly', 'excel_errors' => $excel_data_format_errors]);
        }

        $data->each(function ($value) use ($employee_email, $bkash_number, $manager_member) {
            $profile = $this->profileRepo->checkExistingEmail($value->$employee_email);
            /** @var Member $member */
            $member = $profile->member;
            /** @var BusinessMember $business_member */
            $business_member = $member->activeBusinessMember->first();

            $this->coWorkerBkashAccRequester->setBusinessMember($business_member)
                ->setBkashNumber($value->$bkash_number)
                ->setManagerMember($manager_member)
                ->createOrUpdate();
        });

        unlink($file_path);
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param Request $request
     */
    public function bulkBakshInfoReport($business, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getActiveBusinessMember();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($business_members->get(), new CoWorkerBkashNumberReportTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        return (new BulkBkashNumberExcel)->setEmployeeData($employees->toArray())->download();
    }
}