<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\ExpenseIncome\ExpenseIncome;
use Throwable;

class IncomeExpenseController extends Controller
{
    public function index(Request $request, ExpenseIncome $expenseIncome)
    {
        try {
            $this->validate($request, [
                'frequency' => 'required|in:week,month,year,day'
            ]);
            $data = $expenseIncome->setRequest($request)->setPartner($request->partner)->dashboard();
            return api_response($request, $data, 200, ['data'=>$data]);
        } catch (ValidationException $e) {
            dd($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
