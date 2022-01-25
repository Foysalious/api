<?php namespace App\Http\Controllers\Partner\Webstore;

use App\Exceptions\DoNotReportException;
use App\Sheba\InventoryService\Services\ProductService;
use App\Sheba\Partner\Webstore\WebstoreBannerSettings;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\WebstoreBannerTransformer;
use App\Transformers\Partner\WebstoreSettingsTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;
use Sheba\Dal\WebstoreBanner\Model as WebstoreBanner;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Partner\Webstore\WebstoreSettingsUpdateRequest;
use Sheba\Subscription\Partner\Access\AccessManager;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;
use Throwable;

class WebstoreSettingsController extends Controller
{
    use ModificationFields, FileManager, CdnFileManager;

    public function index(Request $request)
    {
        $settings = $this->getWebstoreSettingsData($request);
        return api_response($request, $settings, 200, ['webstore_settings' => $settings]);
    }

    public function indexV2(Request $request, ProductService $productService)
    {
        $settings = $this->getWebstoreSettingsData($request);
        $partner = resolvePartnerFromAuthMiddleware($request);
        $settings['published_products'] = $productService->setPartnerId($partner->id)->getWebstorePublishedProductCount();
        return http_response($request, $settings, 200, ['webstore_settings' => $settings]);
    }

    /**
     * @param Request $request
     * @param WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest
     * @return JsonResponse
     * @throws AccessRestrictedExceptionForPackage
     * @throws DoNotReportException
     */
    public function update(Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $this->updateWebstoreSettings($request, $webstoreSettingsUpdateRequest);
        return api_response($request, null, 200, ['message' => 'Successful']);
    }

    public function updateV2(Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $this->updateWebstoreSettings($request, $webstoreSettingsUpdateRequest);
        return http_response($request, null, 200, ['message' => 'Successful']);
    }


    /**
     * @param Request $request
     * @param WebstoreBannerSettings $webstoreBannerSettings
     * @return JsonResponse
     */
    public function bannerList(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $list = $this->getBannerList($request, $webstoreBannerSettings);
        return api_response($request, null, 200, ['data' => $list]);
    }

    public function bannerListV2(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $list = $this->getBannerList($request, $webstoreBannerSettings);
        return http_response($request, null, 200, ['data' => $list]);
    }

    /**
     * @param Request $request
     * @param WebstoreBannerSettings $webstoreBannerSettings
     * @return JsonResponse
     */
    public function updateBanner(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $banner_settings_updated = $this->updateBannerSettings($request, $webstoreBannerSettings);
        if (!$banner_settings_updated) {
            return api_response($request, null, 400, ['message' => 'Banner Settings not found']);
        } else {
            return api_response($request, null, 200, ['message' => 'Banner Settings Updated Successfully']);
        }
    }

    public function updateBannerV2(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $banner_settings_updated = $this->updateBannerSettings($request, $webstoreBannerSettings);
        if (!$banner_settings_updated) {
            return http_response($request, null, 400, ['message' => 'Banner Settings not found']);
        } else {
            return http_response($request, null, 200, ['message' => 'Banner Settings Updated Successfully']);
        }
    }

    public function updateBannerV3($id, Request $request)
    {
        $this->validate($request, ['image' => 'required_without:banner_id', 'banner_id' => 'required_without:image']);
        $partner = resolvePartnerFromAuthMiddleware($request);
        $manager_resource = resolveManagerResourceFromAuthMiddleware($request);
        $this->setModifier($manager_resource);
        $banner_id = $request->banner_id;
        if ($request->file('image')) $banner_id = $this->createWebstoreBanner($request->file('image'), $request->title);
        $data = [
            'banner_id' => $banner_id,
            'title' => $request->title,
            'description' => $request->description,
            'is_published' => $request->is_published
        ];
        $banner_settings_updated = $this->updateBannerSettingsV3($id, $partner, $data);
        if (!$banner_settings_updated) {
            return http_response($request, null, 400, ['message' => 'Banner Settings not found']);
        } else {
            return http_response($request, null, 200, ['message' => 'Banner Settings Updated Successfully']);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, ['image' => 'required_without:banner_id', 'banner_id' => 'required_without:image', 'is_published' => 'required']);
            $partner = resolvePartnerFromAuthMiddleware($request);
            $manager_resource = resolveManagerResourceFromAuthMiddleware($request);
            $this->setModifier($manager_resource);
            $banner_id = $request->banner_id;
            if ($request->file('image')) $banner_id = $this->createWebstoreBanner($request->file('image'), $request->title);
            $data = [
                'partner_id' => $partner->id,
                'title' => $request->title,
                'description' => $request->description,
                'is_published' => $request->is_published,
                'banner_id' => $banner_id
            ];

            /** @var WebstoreBannerSettings $webstoreBannerSettings */
            $webstoreBannerSettings = app(WebstoreBannerSettings::class);
            $webstoreBannerSettings->setData($data)->store();
            return http_response($request, null, 200, ['message' => 'Successful']);
        } catch (\Exception $e) {
            return http_response($request, null, 400, ['message' => 'Please Provide the required fields']);
        }
    }

    public function createWebstoreBanner($file, $filename)
    {
        /** @var UploadedFile $avatar */
        /** @var string $avatar_filename */
        list($avatar, $avatar_filename) = $this->makeWebstoreBanner($file, $filename);
        $banner_link = $this->saveFileToCDN($avatar, getWebstoreBannerFolder(), $avatar_filename);
        $data = [
            'image_link' => $banner_link,
            'small_image_link' => $banner_link,
            'is_published' => 1,
            'is_published_for_sheba' => 0
        ];
        $banner = WebstoreBanner::create($this->withCreateModificationField($data));
        return $banner->id;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBanners(Request $request)
    {
        $partner = resolvePartnerFromAuthMiddleware($request);
        $partnerBanners = PartnerWebstoreBanner::where('partner_id', $partner->id)->get();
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($partnerBanners, new WebstoreBannerTransformer());
        $banners = $fractal->createData($resource)->toArray()['data'];
        return http_response($request, $resource, 200, ['banners' => $banners]);
    }

    private function getWebstoreSettingsData(Request $request)
    {
        $partner = resolvePartnerFromAuthMiddleware($request);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($partner, new WebstoreSettingsTransformer());
        return $fractal->createData($resource)->toArray()['data'];
    }

    /**
     * @param Request $request
     * @param WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest
     * @throws AccessRestrictedExceptionForPackage
     * @throws DoNotReportException
     */
    private function updateWebstoreSettings(Request $request, WebstoreSettingsUpdateRequest $webstoreSettingsUpdateRequest)
    {
        $partner = resolvePartnerFromAuthMiddleware($request);
        $this->validate($request, [
            'is_webstore_published' => 'sometimes|numeric|between:0,1', 'name' => 'sometimes|string',
            'sub_domain' => 'sometimes|string', 'delivery_charge' => 'sometimes|integer|digits_between:1,5'
        ],
            [
                'delivery_charge.digits_between' => 'ডেলিভারি চার্জ ৫ সংখ্যার মধ্যে হওয়া আবশ্যক।'
            ]);
        $is_webstore_published = 0;
        $partner_id = $partner->id;
        $this->setModifier($request->manager_resource);
        $webstoreSettingsUpdateRequest->setPartner($partner);
        if ($request->has('is_webstore_published')) {
            if ($request->is_webstore_published) AccessManager::checkAccess(AccessManager::Rules()->POS->ECOM->WEBSTORE_PUBLISH, $partner->subscription->getAccessRules());
            $webstoreSettingsUpdateRequest->setIsWebstorePublished($request->is_webstore_published);
            $is_webstore_published = 1;
        }
        if ($request->has('name')) $webstoreSettingsUpdateRequest->setName($request->name);
        if ($request->has('sub_domain')) $webstoreSettingsUpdateRequest->setSubDomain($request->sub_domain);
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
    }


    private function getBannerList(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $partner = resolvePartnerFromAuthMiddleware($request);
        return $webstoreBannerSettings->getBannerList($partner);
    }

    private function updateBannerSettings(Request $request, WebstoreBannerSettings $webstoreBannerSettings)
    {
        $partner = resolvePartnerFromAuthMiddleware($request);
        $partner_id = $partner->id;
        $manager_resource = resolveManagerResourceFromAuthMiddleware($request);
        $this->setModifier($manager_resource);
        $banner_settings = PartnerWebstoreBanner::where('partner_id', $partner_id)->first();
        if (!$banner_settings) {
            return false;
        } else {
            $webstoreBannerSettings->setBannerSettings($banner_settings)->setData($request->all())->update();
            return true;
        }
    }

    private function updateBannerSettingsV3($id, $partner, $data)
    {
        $banner_settings = PartnerWebstoreBanner::where('id', $id)->where('partner_id', $partner->id)->first();
        if (!$banner_settings) {
            return false;
        } else {
            /** @var WebstoreBannerSettings $webstoreBannerSettings */
            $webstoreBannerSettings = app(WebstoreBannerSettings::class);
            $webstoreBannerSettings->setBannerSettings($banner_settings)->setData($data)->update();
            return true;
        }
    }

    protected function makeWebstoreBanner($file, $name): array
    {
        $filename = $this->uniqueFileName($file, $name);
        return [$file, $filename];
    }
}
