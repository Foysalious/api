<?php namespace App\Repositories;

use App\Models\TopUpOrder;
use Illuminate\Http\Request;
use Sheba\TopUp\TopUpFailedReason;

class VendorRepository
{
    /**
     * @param Request $request
     * @return mixed
     */
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

        if ($request->search) {
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

    /**
     * @param $topupID
     * @param Request $request
     * @return array|null
     */
    public function topUpHistoryDetails($topupID, Request $request)
    {
        /** @var TopUpOrder $topup */
        if ($topup = $request->vendor->topups()->find($topupID)) {
            $failed_reason = (new TopUpFailedReason())->setTopup($topup);
            return [
                'id' => $topup->id,
                'transaction_id' => $topup->transaction_id,
                'mobile' => $topup->payee_mobile,
                'name' => $topup->payee_name ? $topup->payee_name : 'N/A',
                'amount' => (double)$topup->amount,
                'operator' => $topup->vendor->name,
                'status' => $topup->status,
                'failed_reason' => $failed_reason->getFailedReason(),
                'created_at' => $topup->created_at->toDateTimeString()
            ];
        } else {
            return null;
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function details(Request $request)
    {
        return ['data' => ['name' => $request->vendor->name, 'balance' => (double)$request->vendor->wallet]];
    }
}
