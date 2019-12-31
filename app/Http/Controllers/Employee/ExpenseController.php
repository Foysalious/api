<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\FuelLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use Sheba\Business\Support\Creator;
use Sheba\Dal\Expense\Expense;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Employee\ExpenseRepo;

class ExpenseController extends Controller
{
    /** @var SupportRepositoryInterface */
    private $repo;
    private $expense_repo;
    use ModificationFields;
    use FilesAttachment;

    public function __construct(SupportRepositoryInterface $repo, ExpenseRepo $expense_repo)
    {
        $this->repo = $repo;
        $this->expense_repo = $expense_repo;
    }

    public function index(Request $request, MemberRepositoryInterface $member_repository)
    {
        try {
            $this->validate($request, [
                'status' => 'string|in:open,closed',
                'limit' => 'numeric',
                'offset' => 'numeric',
                'start_date' => 'string',
                'end_date' => 'string',
            ]);

            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);

            $data = $this->expense_repo->index($request, $business_member);

            return api_response($request, $data, 200, $data);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, MemberRepositoryInterface $member_repository)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|string',
                'remarks' => 'string',
                'type' => 'string',
                'start_date' => 'string',
                'end_date' => 'string',
                'file' => 'file',
            ]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->where('id', $business_member['member_id'])->first();

            $data =  $this->expense_repo->store($request, $member);

            return api_response($request, $data, 200, $data);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $expense)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);

            $data = $this->expense_repo->show($request, $expense);

            return $data ?
                api_response($request, $expense, 200, $data)
                : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update(Request $request, $expense)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|string',
                'remarks' => 'string',
                'type' => 'string',
            ]);

            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);

            $data = $this->expense_repo->update($request, $expense);

            return $data ?
                api_response($request, $expense, 200, $data)
                : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function delete(Request $request, $expense)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);

            $data = $this->expense_repo->delete($request, $expense);
            return $data ?
                api_response($request, $expense, 200)
                : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}