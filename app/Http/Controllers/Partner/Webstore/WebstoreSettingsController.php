<?php namespace App\Http\Controllers\Partner\Webstore;

use App\Models\Partner;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\WebstoreSettingsTransformer;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Partner\Webstore\WebstoreSettingsUpdateRequest;

class WebstoreSettingsController extends Controller
{
    public function index($partner, Request $request)
    {
        $partner = $request->partner;
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($partner, new WebstoreSettingsTransformer());
        $settings = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $settings, 200, ['webstore_settings' => $settings]);
    }

    public function update($partner, Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $this->validate($request, [
                'is_webstore_published' => 'sometimes|numeric|between:0,1', 'name' => 'sometimes|string',
                'sub_domain' => 'sometimes|string', 'delivery_charge' => 'sometimes|numeric'
        ]);
        $webstoreSettingsUpdateRequest->setPartner($partner);
        if ($request->has('is_webstore_published')) {
            if (!$this->canPublishWebstore($request->partner)) return api_response($request, null,400, ['message' => 'You are not authorized to proceed with this request']);
            $webstoreSettingsUpdateRequest->setIsWebstorePublished($request->is_webstore_published);
        }
        if ($request->has('name')) $webstoreSettingsUpdateRequest->setName($request->name);
        if ($request->has('sub_domain')) {
            if ($this->subDomainAlreadyExist($request->sub_domain)) return api_response($request, null,400, ['message' => 'এই লিংক-টি ইতোমধ্যে ব্যবহৃত হয়েছে!']);
            $webstoreSettingsUpdateRequest->setSubDomain($request->sub_domain);
        }
        if ($request->has('delivery_charge')) $webstoreSettingsUpdateRequest->setDeliveryCharge($request->delivery_charge);
        if ($request->has('has_webstore')) $webstoreSettingsUpdateRequest->setHasWebstore($request->has_webstore);
        $webstoreSettingsUpdateRequest->update();
        return api_response($request, null,200, ['message' => 'Webstore Settings Updated Successfully']);

    }

    private function subDomainAlreadyExist($sub_domain)
    {
        if (Partner::where('sub_domain', $sub_domain)->exists()) return true;
        return false;
    }

    private function canPublishWebstore($partner)
    {
        $rules = $partner->subscription_rules;
        if (is_string($rules)) $rules = json_decode($rules, true);
        if (!$partner->is_webstore_published && (!isset($rules->access_rules->pos->ecom) || !$rules->access_rules->pos->ecom->webstore_publish)) return false;
        return true;
    }
}
