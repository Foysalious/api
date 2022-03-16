<?php

namespace App\Http\Controllers;


use App\Sheba\WebstoreSetting\WebstoreSettingService;
use Illuminate\Http\Request;


class WebstoreSettingController extends Controller
{
    public function __construct(WebstoreSettingService $webstoreSettingService)
    {
        $this->webstoreSettingService = $webstoreSettingService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $settings = $this->webstoreSettingService->getallSettings($partner->id);
        return api_response($request, null, 200, ['message' => 'Successful', 'data' => $settings]);
    }

    public function getThemeDetails($partner, Request $request)
    {
        $settings = $this->webstoreSettingService->getThemeDetails($partner);
        return api_response($request, null, 200, $settings);
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setTheme($request->theme_id)
            ->setSettings($request->settings)
            ->store();
        return api_response($request, null, 200);
    }

    public function update(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setTheme($request->theme_id)
            ->setSettings($request->settings)
            ->update();
        return api_response($request, null, 200);
    }

    public function storeSocialSetting(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setFacebook($request->facebook)
            ->setInstagram($request->instagram)
            ->setWhatsapp($request->whatsapp)
            ->setYoutube($request->youtube)
            ->setEmail($request->email)
            ->storeSocialSetting();
        return $response;
    }

    public function updateSocialSetting(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setFacebook($request->facebook)
            ->setInstagram($request->instagram)
            ->setWhatsapp($request->whatsapp)
            ->setYoutube($request->youtube)
            ->setEmail($request->email)
            ->updateSocialSetting();
        return $response;
    }

    public function getSocialSetting(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $socialSettings = $this->webstoreSettingService->getPartnerSocialSettings($partner->id);
        return api_response($request, null, 200, ['message' => 'Successful', 'data' => $socialSettings]);
    }

    public function getSystemDefinedSettings(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $systemDefinedSettings = $this->webstoreSettingService->getSystemDefinedSettings($partner->id);
        return api_response($request, null, 200, ['message' => 'Successful', 'data' => $systemDefinedSettings]);
    }

    public function getBannerSettings(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $bannerSettings = $this->webstoreSettingService->getBannerSettings($partner->id);
        return api_response($request, null, 200, ['message' => 'Successful', 'data' => $bannerSettings]);
    }

    public function getBanner(Request $request, $banner)
    {
        $partner = $request->auth_user->getPartner();
        $bannerSettings = $this->webstoreSettingService->getBanner($partner->id, $banner);
        return api_response($request, null, 200, ['message' => 'Successful', 'data' => $bannerSettings]);
    }

    public function storeBanner(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        if ($request->input('image')) {
            $image = base64_encode(file_get_contents($request->file('image')->path()));
        }
        $this->webstoreSettingService->setPartner($partner->id)->setTitle($request->title)->setDescription($request->description)->setIsPublish($request->is_published)->setImage($image)->storeBanner();
        return api_response($request, null, 200, ['message' => 'Successful']);
    }

    public function updateBanner(Request $request, $banner)
    {
        $partner = $request->auth_user->getPartner();
        if ($request->input('image')) {
            $image = base64_encode(file_get_contents($request->file('image')->path()));
        }
        $this->webstoreSettingService->setPartner($partner->id)->setTitle($request->title)->setDescription($request->description)->setIsPublish($request->is_published)->setImage($image ?? null)->updateBanner($banner);
        return api_response($request, null, 200, ['message' => 'Successful']);
    }

    public function getBannerList(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $banners =   $this->webstoreSettingService->setPartner($partner->id)->setType($request->type)->getBannerList();
        return api_response($request, null, 200, ['message' => 'Successful', 'banners' => $banners]);

    }

    public function getPageDetails(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $pageDetails = $this->webstoreSettingService->setPartner($partner->id)->setType($request->type)->getPageDetails();
        return api_response($request, null, 200, ['message' => 'Successful', 'page-settings' => $pageDetails]);
    }

    public function storePageSettings(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $pageDetails = $this->webstoreSettingService->setPartner($partner->id)->setType($request->type)->setBannerId($request->banner_id)->setTitle($request->title)->setDescription($request->description)->storePageSettings();
        return api_response($request, null, 200, ['message' => 'Successful']);
    }



}
