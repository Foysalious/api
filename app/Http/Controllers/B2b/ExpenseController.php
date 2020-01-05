<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\BusinessMember;
use App\Models\FuelLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use Sheba\Business\Support\Creator;
use Sheba\Dal\Expense\Expense;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Employee\ExpensePdf;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Employee\ExpenseRepo;
use Illuminate\Support\Collection;

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

            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $members = $member_repository->where('id', $business_member['member_id'])->get();

            if ($request->has('department_id')) {
                $members = $members->filter(function ($member, $key) use ($request) {
                    $member->businessMember->department ?  ($member->businessMember->department->id === $request->department_id) : false;
                });
            }

            if ($request->has('employee_id')) $members = $members->filter(function ($value, $key) use ($request) {
                return $value->id == $request->employee_id;
            });

            $expenses = new Collection();

            foreach($members as $member){
                $member_expenses = $this->expense_repo->index($request, $member);
                if($member_expenses) $expenses = $expenses->merge($member_expenses);

                foreach($member_expenses as $expense){
                    $expense['employee_name'] = $member->profile->name;
                    $expense['employee_department'] = $member->businessMember->department ? $member->businessMember->department->name : null;
                    $expense['attachment'] = $this->expense_repo->getAttachments($expense,$request) ? $this->expense_repo->getAttachments($expense,$request) : null;
                }
            }

            $totalExpenseCount = $expenses->count();

            if ($request->has('limit')) $expenses = $expenses->splice($offset, $limit);

            return api_response($request, $expenses, 200, ['expenses' => $expenses, 'total_expenses_count' => $totalExpenseCount]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $expense, Request $request)
    {
        try {
            $business_member = $request->business_member;
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

    public function update($business, $expense, Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                'remarks' => 'string',
                'type' => 'string',
            ]);

            $business_member = $request->business_member;
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

    public function delete($business, $expense, Request $request)
    {
        try {
            $business_member = $request->business_member;
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
        $business_member = BusinessMember::where('business_id', $request->business_member->business_id)
            ->where('member_id', $request->member_id)
            ->first();

        return $pdf->generate($business_member, $request->month, $request->year);
    }
}