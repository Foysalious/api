<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\CoWorker\BulkGrossSalary\BulkGrossSalaryExcel;
use App\Sheba\Business\Salary\Requester as CoWorkerSalaryRequester;
use App\Transformers\Business\CoWorkerGrossSalaryReportTransformer;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Sheba\Business\CoWorker\GrossSalaryExcel;
use Illuminate\Validation\ValidationException;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use App\Models\Business;
use App\Models\Member;
use Throwable;
use Excel;

class CoWorkerGrossSalaryController extends Controller
{
    use ModificationFields;

    /** @var ProfileRepositoryInterface $profileRepo */
    private $profileRepo;
    /** @var CoWorkerSalaryRequester $coWorkerSalaryRequester */
    private $coWorkerSalaryRequester;

    public function __construct(ProfileRepositoryInterface $profile_repo, CoWorkerSalaryRequester $co_worker_salary_requester)
    {
        $this->profileRepo = $profile_repo;
        $this->coWorkerSalaryRequester = $co_worker_salary_requester;
    }

    public function bulkGrossSalaryUpload($business, Request $request)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);
            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();
            if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

            $manager_member = $request->manager_member;
            $this->setModifier($manager_member);

            $file = Excel::selectSheets(GrossSalaryExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
            $data = Excel::selectSheets(GrossSalaryExcel::SHEET)->load($file_path)->get();

            $data = $data->filter(function ($row) {
                return ($row->employee_email && $row->gross_salary);
            });

            $employee_email = GrossSalaryExcel::USERS_MAIL_COLUMN_TITLE;
            $gross_salary = GrossSalaryExcel::USERS_SALARY_COLUMN_TITLE;

            $data->each(function ($value) use ($employee_email, $gross_salary, $manager_member) {
                $profile = $this->profileRepo->checkExistingEmail($value->$employee_email);
                /** @var Member $member */
                $member = $profile->member;
                /** @var BusinessMember $business_member */
                $business_member = $member->activeBusinessMember->first();
                $this->coWorkerSalaryRequester->setBusinessMember($business_member)
                    ->setGrossSalary($value->$gross_salary)
                    ->setManagerMember($manager_member)
                    ->setIsForBulkGrossSalary(true)
                    ->createOrUpdate();
            });
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            dd($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param $business
     * @param Request $request
     */
    public function grossSalaryReport($business, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getActiveBusinessMember();

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $employees = new Collection($business_members->get(), new CoWorkerGrossSalaryReportTransformer());
        $employees = collect($manager->createData($employees)->toArray()['data']);

        return (new BulkGrossSalaryExcel)->setEmployeeData($employees->toArray())->download();
    }
}