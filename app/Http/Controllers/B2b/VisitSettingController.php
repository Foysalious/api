<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

class VisitSettingController extends Controller
{
    public function settings(Request $request)
    {
        $this->validate($request, [
            'is_enable_employee_visit' => 'required|in:1,0'
        ]);
        /** @var Business $business */
        $business = $request->business;
        $business->update(['is_enable_employee_visit' => (int)$request->is_enable_employee_visit]);
        return api_response($request, null, 200);
    }

    public function getSettings(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        $employee_visit = ['is_enable_employee_visit' => $business->is_enable_employee_visit];
        return api_response($request, null, 200, ['employee_visit' => $employee_visit]);
    }
}