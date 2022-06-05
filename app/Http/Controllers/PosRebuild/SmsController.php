<?php namespace App\Http\Controllers\PosRebuild;

use App\Exceptions\DoNotReportException;
use App\Exceptions\PackageRestrictionException;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\Partner\PackageFeatureCount;
use App\Sheba\PosRebuild\Sms\SmsService;
use App\Sheba\PosRebuild\Sms\Types;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Partner\Feature;
use Sheba\Pos\Notifier\SmsDataGenerator;
use Sheba\Pos\Notifier\SmsHandler;


class SmsController extends Controller
{
    /**
     * @param Request $request
     * @param SmsService $smsService
     * @return JsonResponse
     */
    public function sendSms(Request $request, SmsService $smsService)
    {
        try {
            $this->validate($request, [
                'type' => 'required|in:' . implode(',', Types::get()),
                'type_id' => 'required',
                'partner_id' => 'required'
            ]);

            /** @var SmsDataGenerator $smaData */
            $smaData = app(SmsDataGenerator::class);
            $partner = Partner::find($request->partner_id);
            $data = $smaData->setPartner($partner)->setOrderId($request->type_id)->getData();

            /** @var SmsHandler $smsHandler */
            $smsHandler = app(SmsHandler::class);
            $sms = $smsHandler->setPartner($partner)->setData($data)->getSms();
            $smsCount = $sms->getSmsCountAndEstimationCharge();

            /** @var PackageFeatureCount $packageFeatureCount */
            $packageFeatureCount = app(PackageFeatureCount::class);
            $isEligible = $packageFeatureCount->setPartnerId($request->partner_id)->setFeature(Feature::SMS)->isEligible($smsCount['sms_count']);

            if (!$isEligible) throw new PackageRestrictionException('আপনার নির্ধারিত প্যাকেজের ফ্রি এসএমএস সংখ্যার লিমিট অতিক্রম করেছে। অনুগ্রহ করে প্যাকেজ আপগ্রেড করুন অথবা পরবর্তী মাস শুরু পর্যন্ত অপেক্ষা করুন।', 403);
            $smsService->setPartnerId($request->partner_id)->setType($request->type)->setTypeId($request->type_id)->sendSMS();
            return http_response($request, null, 200, ['message' => 'Sms sent successfully']);
        } catch (Exception $e) {
            return http_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }

    }
}