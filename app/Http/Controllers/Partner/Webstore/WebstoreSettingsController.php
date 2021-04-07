<?php namespace App\Http\Controllers\Partner\Webstore;

use App\Models\Partner;
use App\Sheba\Partner\Webstore\WebstoreBannerSettings;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\WebstoreSettingsTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;
use Sheba\Dal\WebstoreBanner\Model as WebstoreBanner;
use Sheba\ModificationFields;
use Sheba\Partner\Webstore\WebstoreSettingsUpdateRequest;
use Sheba\Subscription\Partner\Access\AccessManager;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;
use Throwable;

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
        $is_webstore_published = 0;
        $partner_id = $request->partner->id;
        $this->setModifier($request->manager_resource);
        $webstoreSettingsUpdateRequest->setPartner($request->partner);
        if ($request->has('is_webstore_published')) {
            if ($request->is_webstore_published) AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->WEBSTORE_PUBLISH, $request->partner->subscription->getAccessRules());

            $webstoreSettingsUpdateRequest->setIsWebstorePublished($request->is_webstore_published);
            $is_webstore_published = 1;
        }
        if ($request->has('name')) $webstoreSettingsUpdateRequest->setName($request->name);
        if ($request->has('sub_domain')) {
            $domain_name = strtolower($request->sub_domain);
            if ($this->subDomainAlreadyExist($domain_name)) return api_response($request, null, 400, ['message' => 'এই লিংক-টি ইতোমধ্যে ব্যবহৃত হয়েছে!']);
            $webstoreSettingsUpdateRequest->setSubDomain($domain_name);
        }
        if ($request->has('delivery_charge')) $webstoreSettingsUpdateRequest->setDeliveryCharge($request->delivery_charge);
        if ($request->has('has_webstore')) $webstoreSettingsUpdateRequest->setHasWebstore($request->has_webstore);
        $webstoreSettingsUpdateRequest->update();

        if ($is_webstore_published) {
            $partner_banner_setting = PartnerWebstoreBanner::where('partner_id', $partner_id)->first();
            if (!$partner_banner_setting) {
                PartnerWebstoreBanner::create($this->withCreateModificationField([
                    'banner_id' => config('partner.webstore_default_banner_id'),
                    'partner_id' => $partner_id,
                    'title' => '',
                    'description' => '',
                    'is_published' => 1
                ]));
            }
        }
        return api_response($request, null, 200, ['message' => 'Successful']);

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
    public function bannerList(Request $request, $partner, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $list = $webstoreBannerSettings->getBannerList();
        return api_response($request, null, 200, ['data' => $list]);
    }


    /**
     * @param Request $request
     * @param $partner
     * @param WebstoreBannerSettings $webstoreBannerSettings
     * @return JsonResponse
     */
    public function updateBanner(Request $request, $partner, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $partner_id = $request->partner->id;
        $this->setModifier($request->manager_resource);
        $banner_settings = PartnerWebstoreBanner::where('partner_id', $partner_id)->first();
        if (!$banner_settings)
            return api_response($request, null, 400, ['message' => 'Banner Settings not found']);
        $webstoreBannerSettings->setBannerSettings($banner_settings)->setData($request->all())->update();
        return api_response($request, null, 200, ['message' => 'Banner Settings Updated Successfully']);
    }
}
