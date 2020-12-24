<?php namespace Sheba\CmDashboard;

use App\Models\Resource;
use Illuminate\Support\Facades\DB;

class ResourceStatusCounter
{
    public function get()
    {
        list($unverified, $verified) = $this->getCountOf('is_verified');
        list($untrained, $trained) = $this->getCountOf('is_trained');
        return [
            'verified' => $verified,
            'unverified' => $unverified,
            'trained' => $trained,
            'untrained' => $untrained,
            'total' => $verified + $unverified,
        ];
    }

    private function getCountOf($column = 'is_verified')
    {
        $counters = Resource::select($column, DB::raw('count(*) as count'))->groupBy($column)->pluck('count', $column)->toArray();
        if(empty($counters)) $counters = [0, 0];
        return $counters;
    }
}