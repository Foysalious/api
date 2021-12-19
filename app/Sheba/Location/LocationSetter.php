<?php namespace Sheba\Location;

use App\Models\HyperLocal;
use App\Models\Location;
use Illuminate\Http\Request;

trait LocationSetter
{
    private $location;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if ($request->filled('location')) {
            $this->location = Location::find($request->location)->id;
        } elseif ($request->filled('lat') && $request->filled('lng')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $this->location = $hyperLocation->location_id;
        }

        $this->location = !is_null($this->location) ? $this->location : 4;
    }
}
