<?php namespace App\Http\Controllers\B2b;


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

            $business_member = $request->business_member;
            if (!$business_member) return api_response($request, null, 401);
            $members = $member_repository->where('id', $business_member['member_id'])->get();

            if ($request->has('employee_id')) $members = $members->filter(function ($value, $key) use ($request) {
                return $value->id == $request->employee_id;
            });

            $expenses = new Collection();

            foreach($members as $member){
                $member_expenses = $this->expense_repo->index($request, $member);
                if($member_expenses) $expenses = $expenses->merge($member_expenses);

                foreach($member_expenses as $expense){
                    $expense['employee_name'] = $member->profile->name;
                }
            }

            return api_response($request, $expenses, 200, ['expenses' => $expenses]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $expense)
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

    public function update(Request $request, $expense)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|string',
                'remarks' => 'string',
                'type' => 'string',
            ]);

            $business_member = $request->business_member;
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
}