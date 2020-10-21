<?php namespace App\Http\Controllers\Pos;

use App\Models\Partner;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Partner\Webstore\WebstoreSettingsUpdateRequest;

class WebstoreSettingsController extends Controller
{
    public function update($partner, Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $this->validate($request, [
                'is_webstore_published' => 'sometimes|numeric|between:0,1', 'name' => 'sometimes|string',
                'sub_domain' => 'sometimes|string', 'delivery_charge' => 'sometimes|numeric'
        ]);
        $webstoreSettingsUpdateRequest->setPartner($partner);
        if ($request->has('is_webstore_published')) $webstoreSettingsUpdateRequest->setIsWebstorePublished($request->is_webstore_published);
        if ($request->has('name')) $webstoreSettingsUpdateRequest->setName($request->name);
        if ($request->has('sub_domain')) $webstoreSettingsUpdateRequest->setSubDomain($request->sub_domain);
        if ($request->has('delivery_charge')) $webstoreSettingsUpdateRequest->setDeliveryCharge($request->delivery_charge);
        $webstoreSettingsUpdateRequest->update();
        return api_response($request, null,200, ['message' => 'Webstore Settings Updated Successfully']);

    }
}
