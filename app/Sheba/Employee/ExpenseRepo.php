<?php namespace Sheba\Employee;


use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use Sheba\Dal\Expense\Expense;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class ExpenseRepo
{
    use ModificationFields;
    use FilesAttachment;

    public function index(Request $request, $member)
    {
        try {
            $expenses = Expense::where('member_id', $member->id)
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')
                ->orderBy('id', 'desc');

            if ($request->has('status')) $expenses = $expenses->where('status', $request->status);

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }

            $expenses = $expenses->get();

            return $expenses;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function store(Request $request, $member)
    {
        try {


            $expense = new Expense;
            $expense->amount = $request->amount;
            $expense->member_id = $member->id;
            $expense->remarks = $request->remarks;
            $expense->type = $request->type;
            $expense->save();

            if ($request['file']) {
                $this->storeAttachment($expense, $request, $member);
            }

            return ['expense' => ['id' => $expense->id]];
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function show(Request $request, $expense)
    {
        try {
            $expense = Expense::where('id', $expense)
                ->orderBy('created_at', 'DESC')
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')->first();

            if (!$expense) return false;

            $expense['date'] = $expense->created_at ? $expense->created_at->format('M d') : null;
            $expense['time'] = $expense->created_at ? $expense->created_at->format('h:i A') : null;
            $expense['can_edit'] = $request->has('can_edit') ? 1 : 0;

            if ($this->getAttachments($expense, $request)) $expense['attachment'] = $this->getAttachments($expense, $request);

            return ['expense' => $expense];
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function update(Request $request, $expense, $member)
    {
        try {
            $expense = Expense::find($expense);
            if (!$expense) return false;

            $expense->amount = $request->amount;
            $expense->remarks = $request->remarks;
            $expense->type = $request->type;
            $expense->save();

            if ($request['file']) {
                $expense->attachments()->detach();
                $this->storeAttachment($expense, $request, $member);
            }

            return ['expense' => ['id' => $expense->id]];
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function delete(Request $request, $expense)
    {
        try {
            $expense = Expense::find($expense);
            if (!$expense) return false;

            $expense->delete();

            return true;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function storeAttachment(Expense $expense, Request $request, $member)
    {
        try {
            $data = $this->storeAttachmentToCDN($request->file('file'));
            $attachment = $expense->attachments()->save(new Attachment($this->withBothModificationFields($data)));
            return $attachment;
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return false;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function getAttachments(Expense $expense, Request $request)
    {
        try {
            if (!$expense) return false;
            $attachment = Attachment::where('attachable_type', get_class($expense))
                ->where('attachable_id', $expense->id)
                ->orderBy('created_at', 'DESC')
                ->select('id', 'title', 'file', 'file_type', 'created_at')
                ->first();
            return $attachment ? $attachment : false;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}