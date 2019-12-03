<?php namespace App\Http\Controllers\Schedule;


use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Partner;
use Illuminate\Http\Request;
use Sheba\Schedule\ScheduleSlot;

class ScheduleTimeController extends Controller
{

    public function index(Request $request, ScheduleSlot $slot)
    {
        $this->validate($request, [
            'for' => 'sometimes|required|string|in:manager,customer',
            'category' => 'sometimes|required|numeric',
            'partner' => 'sometimes|required|numeric',
            'limit' => 'sometimes|required|numeric:min:1'
        ]);
        if ($request->has('category')) $slot->setCategory(Category::find($request->category));
        if ($request->has('partner')) $slot->setPartner(Partner::find($request->partner));
        if ($request->has('limit')) $slot->setLimit($request->limit);
        if ($request->has('for')) $slot->setPortal($request->for);
        $dates = $slot->get();
        return api_response($request, $dates, 200, ['dates' => $dates]);
    }

}