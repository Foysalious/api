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
            list($offset, $limit) = calculatePagination($request);

            $expenses = Expense::where('member_id', $member->id)
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
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')->first();
            if (!$expense) return false;

            $expense['date'] = $expense->created_at ? $expense->created_at->format('M d') : null;
            $expense['time'] = $expense->created_at ? $expense->created_at->format('h:i A') : null;

            if($this->getAttachments($expense,$request))  $expense['attachments'] = $this->getAttachments($expense,$request);

            return ['expense' => $expense];
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function update(Request $request, $expense)
    {
        try {
            $expense = Expense::find($expense);
            if (!$expense) return false;

            $expense->amount = $request->amount;
            $expense->remarks = $request->remarks;
            $expense->type = $request->type;
            $expense->save();

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
            $this->setModifier($member);
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
            $attaches = Attachment::where('attachable_type', get_class($expense))->where('attachable_id', $expense->id)->get();
            $attach_lists = [];
            foreach ($attaches as $attach) {
                array_push($attach_lists, [
                    'id' => $attach->id,
                    'title' => $attach->title,
                    'file' => $attach->file,
                    'file_type' => $attach->file_type,
                ]);
            }
            if (count($attach_lists) > 0) return $attach_lists;
            else  return false;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}