<?php namespace App\Sheba\Queries\SbuDashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerBasedOnOrder
{
    private $today;
    private $hasOrderQuery;

    public function __construct()
    {
        $this->today = Carbon::today()->toDateString();
        $this->hasOrderQuery = function ($query) {
            $query->from('orders as o')->whereRaw('orders.customer_id=o.customer_id')
                ->where('o.created_at', '<', $this->today)->limit(1);
        };
    }

    public function newUser()
    {
        return $this->getBaseQuery()->whereNotExists($this->hasOrderQuery)->get()->all();
    }

    public function returningUser()
    {
        return $this->getBaseQuery()->whereExists($this->hasOrderQuery)->get()->all();
    }

    private function getBaseQuery()
    {
        return DB::table('orders')->where('created_at', '>=', $this->today)
            ->selectRaw('count(distinct(orders.customer_id)) as total');
    }
}
