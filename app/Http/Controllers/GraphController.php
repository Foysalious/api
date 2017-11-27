<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Charts\SalesGrowth;
use Carbon\Carbon;
use Validator;

class GraphController extends Controller
{
    public function getSalesGraph($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'month' => 'sometimes|required|integer|between:1,12',
                'year' => 'sometimes|required|integer|min:2017'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $month = $year = null;
            if ($request->has('month')) {
                $month = $request->month;
            }
            if ($request->has('year')) {
                $year = $request->year;
            }
            $partner = $request->partner;
            $breakdown = ((new SalesGrowth($partner, $month, $year))->get());
            return api_response($request, $breakdown, 200, ['breakdown' => $breakdown]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getOrdersGraph($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'month' => 'sometimes|required|integer|between:1,12',
                'year' => 'sometimes|required|integer|min:2017'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $partner = $request->partner;
            $month = $year = null;
            if ($request->has('month')) {
                $month = $request->month;
            }
            if ($request->has('year')) {
                $year = $request->year;
            }
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
            $partner_orders = $partner->partner_orders;
            $day_orders = $partner_orders->groupBy('created_at.day')
                ->map(function ($item, $key) {
                    return $item->count();
                })->sortBy(function ($item, $key) {
                    return $key;
                });
            $breakdown = $breakdown->map(function ($item, $key) use ($day_orders) {
                return $day_orders->has($key) ? $day_orders->get($key) : 0;
            });
            return api_response($request, $breakdown, 200, ['breakdown' => $breakdown]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}