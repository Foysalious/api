<?php namespace App\Http\Controllers\Partner\Webstore;

use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Helpers\TimeFrame;
use Sheba\Partner\Webstore\WebstoreDashboard;

class WebstoreDashboardController extends Controller
{
    public function getDashboard(Request $request, WebstoreDashboard $webstoreDashboard, TimeFrame $time_frame)
    {
        $this->validate($request, [
            'frequency' => 'required|string|in:day,week,month,quarter,year',
            'date'      => 'required_if:frequency,day,quarter|date',
            'week'      => 'required_if:frequency,week|numeric',
            'month'     => 'required_if:frequency,month|numeric',
            'year'      => 'required_if:frequency,month,year|numeric',
        ]);
        $partner = resolvePartnerFromAuthMiddleware($request);
        $partner = Partner::find((int)$partner->id);
        $dashboard = $webstoreDashboard->setPartner($partner)
            ->setFrequency($request->frequency)
            ->setYear($request->year)
            ->setMonth($request->month)
            ->setWeek($request->week)
            ->setDate($request->date)
            ->get();
        return make_response($request,null, 200, ['webstore_dashboard' => $dashboard]);
    }
}
