<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartnerOrderPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'year' => 'sometimes|required|numeric',
                'month' => 'sometimes|required|numeric',
                'date' => 'sometimes|required|string'
            ]);
            $year = $request->year;
            $month = $request->month;
            $date = $request->date;
            $collections = $request->partner->payments->where('created_by_type', 'App\\Models\\Resource')->filter(function ($payment) use ($date, $month, $year) {
                if ($date || (!$date && !$month && !$year)) {
                    return $payment->created_at->format('Y-m-d') == $date;
                } elseif ($month) {
                    return $year ? $payment->created_at->month == $month && $payment->created_at->year = $year : $payment->created_at->month == $month;
                } elseif ($year) {
                    return $month ? $payment->created_at->month == $month && $payment->created_at->year = $year : $payment->created_at->year == $year;
                }
            })->where('transaction_type', 'Debit')->sortByDesc('id');
            $final = $this->getCollectionInformation($collections);
            return count($final) > 0 ? api_response($request, $final, 200, ['collections' => $final]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getCollectionInformation($collections)
    {
        $final =collect();
        $collections->each(function ($collection) use (&$final) {
            $profile = (Resource::find($collection->created_by))->profile;
            $created_at_timestamp = $collection->created_at->timestamp;
            $collection = collect($collection)->only(['id', 'partner_order_id', 'amount', 'created_by', 'created_at', 'log', 'method']);
            $collection->put('resource_name', $profile->name);
            $collection->put('resource_mobile', $profile->mobile);
            $collection->put('resource_picture', $profile->pro_pic);
            $collection->put('created_at_timestamp', $created_at_timestamp);
            $final->push($collection);
        });
        return $final;
    }

}