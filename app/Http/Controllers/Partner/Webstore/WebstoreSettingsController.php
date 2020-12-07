<?php namespace App\Http\Controllers\Partner\Webstore;

use App\Models\Partner;
use App\Sheba\Partner\Webstore\WebstoreBannerSettings;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\WebstoreSettingsTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ModificationFields;
use Sheba\Partner\Webstore\WebstoreSettingsUpdateRequest;
use Sheba\Subscription\Partner\Access\AccessManager;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;

class WebstoreSettingsController extends Controller
{
    use ModificationFields;

    public function index($partner, Request $request)
    {
        $partner = $request->partner;
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($partner, new WebstoreSettingsTransformer());
        $settings = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $settings, 200, ['webstore_settings' => $settings]);
    }

    /**
     * @param $partner
     * @param Request $request
     * @param WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest
     * @return JsonResponse
     * @throws AccessRestrictedExceptionForPackage
     */
    public function update($partner, Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $this->validate($request, [
                'is_webstore_published' => 'sometimes|numeric|between:0,1', 'name' => 'sometimes|string',
                'sub_domain' => 'sometimes|string', 'delivery_charge' => 'sometimes|numeric'
        ]);
        $webstoreSettingsUpdateRequest->setPartner($partner);
        if ($request->has('is_webstore_published')) {
            AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->WEBSTORE_PUBLISH, $request->partner->subscription->getAccessRules());
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

    /**
     * @param Request $request
     * @param $partner
     * @param WebstoreBannerSettings $webstoreBannerSettings
     * @return JsonResponse
     */
    public function storeBanner(Request $request, $partner, WebstoreBannerSettings $webstoreBannerSettings)
    {
        try {
            $this->validate($request, [
                'banner_id' => 'required',
                'title' => 'string',
                'description' => 'string',
                'is_published' => 'sometimes|in:1,0',
            ]);

            $this->setModifier($request->manager_resource);
            $data = [
                'partner_id' => $request->partner->id,
                'banner_id' => $request->banner_id,
                'title' => $request->title ?: null,
                'description' => $request->description ?: null,
                'is_published' => $request->is_published ?: 0,
            ];

            $webstoreBannerSettings->setData($data)->store();
            return api_response($request, null, 200, ['msg' => 'Banner Settings Created Successfully']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
