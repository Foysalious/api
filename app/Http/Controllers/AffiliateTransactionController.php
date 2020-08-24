<?php

namespace App\Http\Controllers;

use App\Repositories\AffiliateTransactionRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AffiliateTransactionController extends Controller
{
    /**
     * @param Request $request
     * @param AffiliateTransactionRepository $transactionRepository
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getTransactionByCategory(Request $request, AffiliateTransactionRepository $transactionRepository)
    {
        try {
            $this->validate($request, [
                'start_date' => 'date|required',
                'end_date'   => 'date|required'
            ]);
            $transaction_records  = $transactionRepository->setDates($request)->setAffiliate($request->affiliate)->getHistory();
            return api_response($request, $transaction_records, 200, ['data' => $transaction_records]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
