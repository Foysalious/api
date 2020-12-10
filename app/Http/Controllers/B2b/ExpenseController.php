<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\TopUpOrder;
use App\Transformers\Business\ExpenseTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Attachments\FilesAttachment;
use Sheba\Business\CoWorker\Statuses;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'status' => 'string|in:open,closed',
            'limit' => 'numeric', 'offset' => 'numeric',
            'start_date' => 'string',
            'end_date' => 'string'
        ]);
        list($offset, $limit) = calculatePagination($request);
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        /** @var Business $business */
        $business = $request->business;
        $business_members = $business->getAccessibleBusinessMember();

        if ($request->has('department_id')) {
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department_id);
                });
            });
        }

        $members_ids = $business_members->pluck('member_id')->toArray();
        $expenses = Expense::whereIn('member_id', $members_ids)->with([
            'member' => function ($query) {
                $query->select('members.id', 'members.profile_id')->with([
                    'profile' => function ($query) {
                        $query->select('profiles.id', 'profiles.name', 'profiles.email', 'profiles.mobile');
                    },
                    'businessMember' => function ($q) {
                        $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id')->with([
                            'role' => function ($q) {
                                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                                    'businessDepartment' => function ($q) {
                                        $q->select('business_departments.id', 'business_id', 'name');
                                    }
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->select('id', 'member_id', 'amount', 'created_at', DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(amount) amount'))
            ->groupby('year', 'month', 'member_id')
            ->orderBy('created_at', 'desc');

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
        }
        if (($start_date && $end_date) && !$request->has('key')) {
            $expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        $fractal = new Manager();
        $resource = new Collection($expenses->get(), new ExpenseTransformer());
        $expenses = $fractal->createData($resource)->toArray()['data'];


        if ($request->has('sort_by_employee_id')) $expenses = $this->sortByEmployeeId($expenses, $request->sort_by_employee_id)->values();
        if ($request->has('sort_by_name')) $expenses = $this->sortByName($expenses, $request->sort_by_name)->values();
        if ($request->has('sort_by_department')) $expenses = $this->sortByDepartment($expenses, $request->sort_by_department)->values();
        if ($request->has('sort_by_amount')) $expenses = $this->sortByAmount($expenses, $request->sort_by_amount)->values();
        if ($request->has('search')) $expenses = $this->searchWithEmployeeName($expenses, $request);

        $total_expense_count = count($expenses);
        if ($request->has('limit')) $expenses = collect($expenses)->splice($offset, $limit);
        return api_response($request, $expenses, 200, ['expenses' => $expenses, 'total_expenses_count' => $total_expense_count]);
    }

    /**
     * @param $expenses
     * @param Request $request
     * @return mixed
     */
    private function searchWithEmployeeName($expenses, Request $request)
    {
        return $expenses->filter(function ($expense) use ($request) {
            return str_contains(strtoupper($expense['employee_name']), strtoupper($request->search));
        });
    }

    /**
     * @param $expenses
     * @param string $sort
     * @return mixed
     */
    private function sortByEmployeeId($expenses, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($expenses)->$sort_by(function ($expense, $key) {
            return strtoupper($expense['employee_id']);
        });
    }

    /**
     * @param $expenses
     * @param string $sort
     * @return mixed
     */
    private function sortByName($expenses, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($expenses)->$sort_by(function ($expense, $key) {
            return strtoupper($expense['employee_name']);
        });
    }

    /**
     * @param $expenses
     * @param string $sort
     * @return mixed
     */
    private function sortByDepartment($expenses, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($expenses)->$sort_by(function ($expense, $key) {
            return strtoupper($expense['employee_department']);
        });
    }

    /**
     * @param $expenses
     * @param string $sort
     * @return mixed
     */
    private function sortByAmount($expenses, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($expenses)->$sort_by(function ($expense, $key) {
            return strtoupper($expense['amount']);
        });
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

    public function filterMonth(Request $request)
    {
        try {
            $this->validate($request, [
                'limit' => 'numeric', 'offset' => 'numeric', 'month' => 'numeric',
            ]);
            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $month = $request->month;
            $expenses = $this->expense_repo->filterMonth($month, $request);
            $totalExpenseCount = $expenses->count();
            $totalExpenseSum = $expenses->sum('amount');
            return api_response($request, $expenses, 200, ['expenses' => $expenses, 'total_expenses_count' => $totalExpenseCount, 'total_expenses_sum' => $totalExpenseSum]);

        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}
