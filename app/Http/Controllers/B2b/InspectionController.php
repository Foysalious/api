<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $inspections = Inspection::where('business_id', $business->id)->orderBy('id', 'DESC')->get();

            /*if ($request->has('status')) {
                $members->where(function ($query) use ($request) {
                    $query->whereHas('businessMember.role.businessDepartment', function ($query) use ($request) {
                        $query->where('name', $request->department);
                    });
                });
            }*/
            $inspection_lists = [];
            foreach ($inspections as $inspection) {
                dd($inspection->formTemplates);
                $inspection = [
                    'id' => $inspection->id,
                    'inspection_form' => $inspection->name,
                    'type' => $inspection->mobile,
                    'next_start_date' => $inspection->next_start_date,
                ];
                array_push($inspection_lists, $inspection);
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}