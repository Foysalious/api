<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\BusinessMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Attachments\FilesAttachment;
use Sheba\Dal\Expense\Expense as Expense;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Employee\ExpensePdf;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Employee\ExpenseRepo;
use Throwable;

class ExpenseController extends Controller
{
    use ModificationFields, FilesAttachment;

    /** @var SupportRepositoryInterface $repo */
    private $repo;
    /** @var ExpenseRepo $expense_repo */
    private $expense_repo;

    /**
     * ExpenseController constructor.
     * @param SupportRepositoryInterface $repo
     * @param ExpenseRepo $expense_repo
     */
    public function __construct(SupportRepositoryInterface $repo, ExpenseRepo $expense_repo)
    {
        $this->repo = $repo;
        $this->expense_repo = $expense_repo;
    }

    public function index(Request $request, MemberRepositoryInterface $member_repository)
    {
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
    }

    public function store(Request $request, MemberRepositoryInterface $member_repository)
    {
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
    }

    public function show(Request $request, $expense)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        $data = $this->expense_repo->show($request, $expense);

        return $data ?
            api_response($request, $expense, 200, $data)
            : api_response($request, null, 404);
    }

    /**
     * @param Request $request
     * @param $expense
     * @return JsonResponse
     */
    public function update(Request $request, $expense)
    {
        $this->validate($request, ['amount' => 'required|string',]);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        $data = $this->expense_repo->update($request, $expense, $business_member);

        return $data ? api_response($request, null, 200, $data) : api_response($request, null, 404);
    }

    public function delete(Request $request, $expense)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        $data = $this->expense_repo->delete($request, $expense);
        return $data ?
            api_response($request, $expense, 200)
            : api_response($request, null, 404);
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
