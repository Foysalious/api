<?php namespace App\Sheba\Queries\SbuDashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerBasedOnOrder
{
    private $table;

    public function __construct()
    {
        $this->table = DB::table('customers');
        $this->today = Carbon::today()->toDateString();
    }

    public function newUser()
    {
        $date = $this->today;
        $data = DB::table('orders')->where('created_at', '>=', $date)->whereNotExists(function ($query) use ($date) {
            $query->from('orders as o')->whereRaw('orders.customer_id=o.customer_id')->where('o.created_at', '<', $date)->limit(1);
        })->selectRaw('count(distinct(orders.customer_id)) as total')->get();
        return $data;
    }

    public function returningUser()
    {
        $date = $this->today;
        $data = DB::table('orders')->where('created_at', '>=', $date)->whereExists(function ($query) use ($date) {
            $query->from('orders as o')->whereRaw('orders.customer_id=o.customer_id')->where('o.created_at', '<', $date)->limit(1);
        })->selectRaw('count(distinct(orders.customer_id)) as total')->get();

        return $data;
    }
}