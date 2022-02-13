<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Repositories\PartnerGeneralSettingRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\PartnerGeneralSetting\Model as PartnerGeneralSetting;


class PartnerGeneralSettingController extends Controller
{
    protected $partnerGeneralSettingRepo;
    protected $artnerGeneralSettingModel;

    public function __construct(PartnerGeneralSettingRepository $partnerGeneralSettingRepo, PartnerGeneralSetting $artnerGeneralSettingModel)
    {
        $this->partnerGeneralSettingRepo = $partnerGeneralSettingRepo;
        $this->artnerGeneralSettingModel = $artnerGeneralSettingModel;
    }

    /**
     * @param Request $request
     * @param $partner
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSMSNotification(Request $request, $partner)
    {
        try {
            $this->validate($request, ['sending_sms' => 'required|boolean']);

            $data = [
                'partner_id' => $partner,
                'payment_completion_sms' => $request->sending_sms
            ];

            $this->partnerGeneralSettingRepo->storeSMSNotificationStatus($data);
            return api_response($request, null, 200);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSMSNotification(Request $request, $partner)
    {
        $status = $this->partnerGeneralSettingRepo->getSMSNotificationStatus($partner);

        return api_response($request, null, 200, ['data' => $status]);
    }
}