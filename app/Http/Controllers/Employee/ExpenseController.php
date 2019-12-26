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

            $expenses = $expenses->get();

            return api_response($request, $expenses, 200, ['expenses' => $expenses]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator, MemberRepositoryInterface $member_repository)
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
            dd($e->getMessage());
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}