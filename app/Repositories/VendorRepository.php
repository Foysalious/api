<?php namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorRepository
{
    public function topUpHistory(Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $topups = $request->vendor->topups();

        if (isset($request->from) && $request->from !== "null") $topups = $topups->whereBetween('created_at', [$request->from . " 00:00:00", $request->to . " 23:59:59"]);
        if (isset($request->vendor_id) && $request->vendor_id !== "null") $topups = $topups->where('vendor_id', $request->vendor_id);
        if (isset($request->status) && $request->status !== "null") $topups = $topups->where('status', $request->status);
        if (isset($request->q) && $request->q !== "null") $topups = $topups->where('payee_mobile', 'LIKE', '%' . $request->q . '%');
//        $total_topups = $topups->count();
//        ->skip($offset)->take($limit)
        $topups = $topups->with('vendor')->orderBy('created_at', 'desc')->get();

        if($request->search) {
            $search_key = $request->search;
            $topups = $topups->filter(function ($topup) use ($search_key) {
                return strpos($topup->payee_mobile, $search_key) !== false;;
            });
        }

        $topup_data = $topups->map(function ($topup) {
            return [
                'mobile' => $topup->payee_mobile,
                'name' => $topup->payee_name ? $topup->payee_name : 'N/A',
                'amount' => (double)$topup->amount,
                'operator' => $topup->vendor->name,
                'status' => $topup->status,
                'created_at' => $topup->created_at->toDateTimeString()
            ];
        });
        return $topup_data;

    }

    public function topUpHistoryDetails($topupID, Request $request)
    {
        $topup = $request->vendor->topups()->find($topupID);
        $topup_data = [
                'mobile' => $topup->payee_mobile,
                'name' => $topup->payee_name ? $topup->payee_name : 'N/A',
                'amount' => (double)$topup->amount,
                'operator' => $topup->vendor->name,
                'status' => $topup->status,
                'created_at' => $topup->created_at->toDateTimeString()
            ];
        return $topup_data;

    }

    public function details(Request $request)
    {
        return ['data' => ['name' => $request->vendor->name, 'balance' => (double)$request->vendor->wallet]];
    }
}
