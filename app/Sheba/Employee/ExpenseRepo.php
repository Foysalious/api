<?php namespace Sheba\Employee;


use App\Models\Attachment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use Sheba\Dal\Expense\Expense;
use Sheba\ModificationFields;

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

    public function filterMonth($month,$request)
    {
        try {
            $date = Carbon::createFromFormat('m', $month);
            $start_date= $date->startOfMonth()->toDateTimeString();
            $end_date=$date->endOfMonth()->toDateTimeString();
            $expenses= Expense::where('member_id',$request->member_id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select('id', 'member_id', 'amount', 'status', 'remarks', 'type', 'created_at')
                ->orderBy('id', 'desc')
                ->get();
            foreach ($expenses as $expense) {
                $expense['employee_name'] = $expense->member->profile->name;
                $expense['employee_department'] = $expense->member->businessMember->department() ? $expense->member->businessMember->department()->name : null;
                $expense['attachment'] = $this->getAttachments($expense, $request) ? $this->getAttachments($expense, $request) : null;
                unset($expense->member);
            }
            return $expenses;
        } catch (\Throwable $e){
            return false;
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
            $expense['can_edit'] = $this->canEdit($expense);

            if ($this->getAttachments($expense, $request)) $expense['attachment'] = $this->getAttachments($expense, $request);

            return ['expense' => $expense];
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function canEdit(Expense $expense)
    {
        $created_at = $expense->created_at;
        if ($created_at->month == 12) $can_edit_until = Carbon::create($created_at->year + 1, 1, 5, 23, 59, 59);
        else $can_edit_until = Carbon::create($created_at->year, $created_at->month + 1, 5, 23, 59, 59);
        return Carbon::now()->lte($can_edit_until) ? 1 : 0;
    }

    public function update(Request $request, $expense, $member)
    {
        try {
            $expense = Expense::find($expense);
            if (!$expense) return false;

            $expense->amount = $request->amount;
            $expense->save();

            if ($request['file']) {
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
                ->get();
            return $attachment ? $attachment : false;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}