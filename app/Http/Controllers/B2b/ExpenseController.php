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
            $expenses = Expense::whereIn('member_id', $members_ids)->with(['member' => function ($query) {
                $query->select('members.id', 'members.profile_id')->with(['profile' => function ($query) {
                    $query->select('profiles.id', 'profiles.name', 'profiles.email', 'profiles.mobile');
                }, 'businessMember' => function ($q) {
                    $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id')->with(['role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with(['businessDepartment' => function ($q) {
                            $q->select('business_departments.id', 'business_id', 'name');
                        }]);
                    }]);
                }]);
            }])->select('id', 'member_id', 'amount', 'created_at', DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(amount) amount'))
                ->groupby('year', 'month', 'member_id')
                ->orderBy('created_at', 'desc');

            $start_date = null;
            $end_date = null;
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            if ($request->has('start_date') && $request->has('end_date')) {
                $start_date = $request->has('start_date') ? $request->start_date : null;
                $end_date = $request->has('end_date') ? $request->end_date : null;
            }

            if ($start_date && $end_date) {
                $expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $expenses = $expenses->get();

            foreach ($expenses as $key => $expense) {
                $member = $expense->member;
                $expense['employee_name'] = $member->profile->name;
                $expense['employee_department'] = $member->businessMember->department() ? $member->businessMember->department()->name : null;
                #$expense['attachment'] = $this->expense_repo->getAttachments($expense, $request) ? $this->expense_repo->getAttachments($expense, $request) : null;
                unset($expense->member);
            }
            $totalExpenseCount = $expenses->count();
            if ($request->has('limit')) $expenses = $expenses->splice($offset, $limit);
            return api_response($request, $expenses, 200, ['expenses' => $expenses, 'total_expenses_count' => $totalExpenseCount]);
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
