<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 10/27/2018
 * Time: 7:00 PM
 */

namespace App\Sheba\Queries\SbuDashboard;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TopUpSummary
{
    private $table;
    private $today;
    private $vendor_table;

    public function __construct()
    {
        $this->table = DB::table('topup_orders');
        $this->vendor_table = DB::table('topup_vendors');
        $this->today = Carbon::today()->toDateString();
    }

    public function todayAmount()
    {
        $data = $this->table->where('created_at', '>=', $this->today)->selectRaw('count(*) as count,sum(amount) as total_amount')->get();
        return [$data[0]->count, $data[0]->total_amount!=null?$data[0]->total_amount:0];
    }


    public function operatorSummary()
    {
        $data = DB::table('topup_vendors')
            ->join('topup_orders', 'topup_orders.vendor_id', '=', 'topup_vendors.id')
            ->where('topup_vendors.is_published', 1)
            ->where('topup_orders.created_at', '>=', $this->today)
            ->selectRaw('sum(topup_orders.amount) as total_recharge, topup_vendors.amount as amount,topup_vendors.name as name')
            ->groupBy('topup_orders.vendor_id')
            ->get();
        $items = DB::table('topup_vendors')->where('is_published', 1)->select(['amount', 'name'])->get();
        $output = [];
        foreach ($items as $key => $item) {
            $output[$key] = $item;
            if (isset($data[$key])) {
                $output[$key]->total_recharge = $data[$key]->total_recharge;
            } else {
                $output[$key]->total_recharge = 0;
            }
        }
        return $output;
    }

}