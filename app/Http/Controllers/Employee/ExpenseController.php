<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\BusinessMember;
use App\Models\FuelLog;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use NumberFormatter;
use Sheba\Attachments\FilesAttachment;
use Sheba\Business\Support\Creator;
use Sheba\Dal\Expense\Expense as Expense;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Employee\ExpensePdf;
use Sheba\Helpers\TimeFrame;
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

            list($offset, $limit) = calculatePagination($request);

            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->where('id', $business_member['member_id'])->first();

            $expenses = $this->expense_repo->index($request, $member);

            if ($request->has('limit')) $expenses = $expenses->splice($offset, $limit);

            $sum = $expenses->sum('amount');

            return api_response($request, $expenses, 200, ['data' => ['expenses' => $expenses, 'sum' => $sum]]);
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

            $data = $this->expense_repo->store($request, $member);

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

            $data = $this->expense_repo->update($request, $expense, $business_member);

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

    public function downloadPdf(Request $request, ExpensePdf $pdf)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);
        $business_member = BusinessMember::where('business_id', $business_member['business_id'])
            ->where('member_id', $business_member['member_id'])
            ->first();

        return $pdf->generate($business_member, $request->month, $request->year);
    }

    public function deleteAttachment(Request $request, $expense, $attachment)
    {

        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        $expense = Expense::find($expense);
        if (!$expense || $expense->member_id != $business_member['member_id']) return api_response($request, null, 403);
        $attachment = Attachment::find($attachment);
        if (!stripos(strtolower($attachment->attachable_type), 'expense') || $attachment->attachable_id != $expense->id) return api_response($request, null, 403);
        $attachment->delete();
        return api_response($request, 1, 200);
    }
}
