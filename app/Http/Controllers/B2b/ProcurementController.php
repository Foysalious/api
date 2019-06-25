<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class ProcurementController extends Controller
{
    use ModificationFields;
    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->setModifier($request->manager_member);
            $request->merge(['member_id' => $request->manager_member->id]);
            /** @var \Sheba\Business\Inspection\Creator $creation_class */
            $creation_class = $create_processor->setType($request->schedule_type)->getCreationClass();
            $inspection = $creation_class->setData($request->all())->setBusiness($request->business)->create();
            return api_response($request, null, 200, ['id' => $inspection->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}