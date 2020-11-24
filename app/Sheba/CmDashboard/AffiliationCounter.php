<?php namespace Sheba\CmDashboard;

use App\Models\Affiliation;
use Illuminate\Support\Facades\DB;

class AffiliationCounter
{
    private $counter;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        $this->counter = [
            'pending'    => 0,
            'converted'  => 0,
            'follow_up'  => 0,
            'cancelled'  => 0,
            'successful' => 0,
            'rejected'   => 0
        ];
    }

    public function get()
    {
        $counts = $this->countQuery()->get()->pluck('count', 'status')->toArray() + $this->counter;
        return $counts;
    }

    private function countQuery()
    {
        return Affiliation::select('status', DB::raw('count(*) as count'))->groupBy('status');
    }
}