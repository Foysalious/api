<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Charts\SalesGrowth;
use Carbon\Carbon;
use Validator;

class GraphController extends Controller
{
    public function getSalesGraph($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'sometimes|required|integer|between:1,12',
                'year' => 'sometimes|required|integer|min:2017'
            ]);
            $breakdown = ((new SalesGrowth($request->partner, $request->month, $request->year))->get());
            return api_response($request, $breakdown, 200, ['breakdown' => $breakdown]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getOrdersGraph($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'sometimes|required|integer|between:1,12',
                'year' => 'sometimes|required|integer|min:2017'
            ]);
            $partner = $request->partner;
            $month = $request->month;
            $year = $request->year;
            $end = Carbon::create($year, $month, null)->endOfMonth();
            $start = Carbon::create($year, $month, null)->startOfMonth();
            $breakdown = collect(array_fill(1, Carbon::create($year, $month, null)->daysInMonth, 0));
            $partner->load(['partner_orders' => function ($q) use ($start, $end) {
                $q->where([
                    ['created_at', '<=', $end],
                    ['created_at', '>=', $start],
                    ['cancelled_at', null]
                ]);
            }]);
            $day_orders = $partner->partner_orders->groupBy('created_at.day')->map(function ($item, $key) {
                return $item->count();
            })->sortBy(function ($item, $key) {
                return $key;
            });
            $breakdown = $breakdown->map(function ($item, $key) use ($day_orders) {
                return $day_orders->has($key) ? $day_orders->get($key) : 0;
            });
            return api_response($request, $breakdown, 200, ['breakdown' => $breakdown]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}