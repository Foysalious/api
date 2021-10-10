<?php

namespace App\Http\Controllers;


use App\Sheba\WebstoreSetting\WebstoreSettingService;
use Illuminate\Http\Request;


class WebstoreSettingController extends Controller
{
    /**
     * @var WebstoreSettingService
     */
    private $webstoreSettingService;

    public function __construct(WebstoreSettingService $webstoreSettingService)
    {
        $this->webstoreSettingService = $webstoreSettingService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $units = $this->webstoreSettingService->getallSettings($partner->id);
        return api_response($request, null, 200, $units);
    }

    public function getThemeDetails(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $units = $this->webstoreSettingService->getThemeDetails($partner->id);
        return api_response($request, null, 200, $units);
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setTheme($request->theme_id)
            ->setSettings($request->settings)
            ->store();
        return $response;
    }

    public function update(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->webstoreSettingService
            ->setPartner($partner->id)
            ->setTheme($request->theme_id)
            ->setSettings($request->settings)
            ->update();
        return $response;
    }

}
