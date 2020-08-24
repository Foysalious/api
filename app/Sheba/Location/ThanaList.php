<?php namespace Sheba\Location;

use App\Models\Thana;

class ThanaList
{
    /**
     * @var Thana
     */
    private $thana;

    public function __construct(Thana $thana)
    {
        $this->thana = $thana;
    }

    public function getAllThana()
    {
        return $this->thana->with(['district'=>function ($q) {
            $q->select('id','name', 'bn_name');
        }])->get();
    }
}