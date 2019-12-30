<?php namespace App\Http\Controllers\Employee;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Business\Support\Creator;
use Sheba\Dal\Expense\Expense;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class ExpenseController extends Controller
{
    /** @var SupportRepositoryInterface */
    private $repo;

    public function __construct(SupportRepositoryInterface $repo)
    {
        $this->repo = $repo;
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

            list($offset, $limit) = calculatePagination($request);

            $expenses = Expense::where('member_id', $business_member['member_id'])
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type')
                ->orderBy('id', 'desc');

            if ($request->has('status')) $supports = $expenses->where('status', $request->status);
            if ($request->has('limit')) $supports = $expenses->skip($offset)->limit($limit);

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }

            $expenses = $expenses->get();

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
            ]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $member = $member_repository->where('id', $business_member['member_id'])->first();

            $expense = new Expense;
            $expense->amount = $request->amount;
            $expense->member_id = $member->id;
            $expense->remarks = $request->remarks;
            $expense->type = $request->type;
            $expense->save();

            return api_response($request, $expense, 200, ['expense' => ['id' => $expense->id]]);
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
            $expense = Expense::where('id', $expense)
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type')->first();

            if (!$expense) return api_response($request, null, 404);

            $expense['date'] = $expense->created_at ? $expense->created_at->format('M d') : null;
            $expense['time'] = $expense->created_at ? $expense->created_at->format('h:i A') : null;
            return api_response($request, $expense, 200, ['expense' => $expense]);
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

            $expense = Expense::find($expense);
            if (!$expense) return api_response($request, null, 404);


            $expense->amount = $request->amount;
            $expense->remarks = $request->remarks;
            $expense->type = $request->type;
            $expense->save();

            return api_response($request, $expense, 200, ['expense' => ['id' => $expense->id]]);
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

            $expense = Expense::find($expense);
            if (!$expense) return api_response($request, null, 404);

            $expense->delete();

            return api_response($request, $expense, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}