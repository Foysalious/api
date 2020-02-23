<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Attachments\FilesAttachment;
use Sheba\Dal\Expense\Expense;
use Sheba\Employee\ExpensePdf;
use Sheba\ModificationFields;
use Sheba\Employee\ExpenseRepo;
use Throwable;
use DB;

class ExpenseController extends Controller
{
    private $expense_repo;
    use ModificationFields;
    use FilesAttachment;

    /**
     * ExpenseController constructor.
     * @param ExpenseRepo $expense_repo
     */
    public function __construct(ExpenseRepo $expense_repo)
    {
        $this->expense_repo = $expense_repo;
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'string|in:open,closed', 'limit' => 'numeric', 'offset' => 'numeric', 'start_date' => 'string', 'end_date' => 'string',
            ]);
            list($offset, $limit) = calculatePagination($request);
            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $members = $request->business->members()->get();

            if ($request->has('department_id')) {
                $members = $members->filter(function ($member, $key) use ($request) {
                    if ($member->businessMember->role) {
                        if ($member->businessMember->department()) {
                            return $member->businessMember->department()->id == $request->department_id;
                        }
                    } else {
                        return false;
                    }
                });
            }
            if ($request->has('employee_id')) $members = $members->filter(function ($value, $key) use ($request) {
                return $value->id == $request->employee_id;
            });

            $members_ids = $members->pluck('id')->toArray();
            $expenses = Expense::whereIn('member_id', $members_ids)
                ->select('id', 'member_id', 'amount', 'created_at')
                ->orderBy('id', 'desc');

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $expenses = $expenses->get();

            $expenses_group_by_month = $expenses->groupBy(function ($expense) {
                return Carbon::parse($expense->created_at)->format('m-y');
            });
            $month_wise_expense_of_members = [];
            foreach ($expenses_group_by_month as $month => $expenses) {
                $expenses = $expenses->groupBy('member_id')->map(function ($expense) {
                    return $expense->sum('amount');
                });
                foreach ($expenses as $member_id => $expense_amount) {
                    array_push($month_wise_expense_of_members, [
                        'month' => $month,
                        'member_id' => $member_id,
                        'total_amount' => $expense_amount
                    ]);
                }
            }
            foreach ($month_wise_expense_of_members as $key => $month_wise_expense_of_member) {
                $member = $this->getMember($month_wise_expense_of_member['member_id']);
                $month_wise_expense_of_members[$key] ['employee_name'] = $member->getIdentityAttribute();
                $month_wise_expense_of_members[$key] ['employee_department'] = $member->businessMember->department() ? $member->businessMember->department()->name : null;
            }

            $totalExpenseCount = count($month_wise_expense_of_members);
            if ($request->has('limit')) $expenses = $expenses->splice($offset, $limit);
            return api_response($request, $expenses, 200, ['expenses' => $month_wise_expense_of_members, 'total_expenses_count' => $totalExpenseCount]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getMember($member_id)
    {
        $member = Member::findOrFail($member_id);
        return $member;
    }

    public function show($business, $expense, Request $request)
    {
        try {
            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $data = $this->expense_repo->show($request, $expense);
            return $data ? api_response($request, $expense, 200, $data) : api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($business, $expense, Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric', 'remarks' => 'string', 'type' => 'string',
            ]);
            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $data = $this->expense_repo->update($request, $expense, $business_member);
            return $data ? api_response($request, $expense, 200, $data) : api_response($request, null, 404);
        } catch (Throwable $e) {
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
            return $data ? api_response($request, $expense, 200) : api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function downloadPdf(Request $request, ExpensePdf $pdf)
    {
        $business_member = BusinessMember::where('business_id', $request->business_member->business_id)->where('member_id', $request->member_id)->first();
        return $pdf->generate($business_member, $request->month, $request->year);
    }
}
