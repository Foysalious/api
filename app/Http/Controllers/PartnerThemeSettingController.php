<?php namespace App\Http\Controllers;

use App\Sheba\PartnerThemeSettingService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class PartnerThemeSettingController extends Controller
{
    /**
     * @var PartnerThemeSettingService
     */
    private $partnerThemeSettingService;

    public function __construct(PartnerThemeSettingService $partnerThemeSettingService)
    {
        $this->partnerThemeSettingService = $partnerThemeSettingService;
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();

        $setting = $this->partnerThemeSettingService->setPartnerId($partner->id)->setThemeID($request->theme_id)->setSettiings($request->settings)->store();
        return api_response($request, null, 200);


    }
}
