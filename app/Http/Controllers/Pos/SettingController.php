<?php namespace App\Http\Controllers\Pos;

use App\Exceptions\Pos\SMS\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPosSetting;
use App\Models\PosCustomer;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\PosSettingRepository;
use Sheba\Pos\Setting\Creator;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Throwable;

class SettingController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param Creator $creator
     * @param PosSettingRepository $repository
     * @return JsonResponse
     */
    public function getSettings(Request $request, Creator $creator, PosSettingRepository $repository)
    {
        try {
            $partner = resolvePartnerFromAuthMiddleware($request);
            $settings = PartnerPosSetting::byPartner($partner->id)->select('id', 'partner_id', 'vat_percentage', 'auto_printing', 'sms_invoice')->first();
            if (!$settings) {
                $data = ['partner_id' => $partner->id];
                $creator->setData($data)->create();
                $settings = PartnerPosSetting::byPartner($partner->id)->select('id', 'partner_id', 'vat_percentage', 'auto_printing', 'sms_invoice')->first();
            }
            $settings->vat_registration_number = $partner->basicInformations->vat_registration_number;
            $settings->vat_reg_number = $partner->basicInformations->is_show_vat_reg_number;
            $settings['has_qr_code'] = ($partner->qr_code_image && $partner->qr_code_account_type) ? 1 : 0;
            removeRelationsAndFields($settings);
            return make_response($request, $settings, 200, ['settings' => $settings]);
        } catch (Throwable $e) {
            logError($e);
            app('sentry')->captureException($e);
            return make_response($request, null, 500,null);
        }
    }

    public function getPrinterSettings(Request $request, Creator $creator, PosSettingRepository $repository)
    {
        try {
            $partner = resolvePartnerFromAuthMiddleware($request);
            $settings = PartnerPosSetting::byPartner($partner->id)->select('partner_id', 'printer_model', 'printer_name', 'auto_printing')->first();
            if (!$settings) {
                $data = ['partner_id' => $partner->id,];
                $creator->setData($data)->create();
                $settings = PartnerPosSetting::byPartner($partner->id)->select('partner_id', 'printer_model', 'printer_name', 'auto_printing')->first();
            }
            removeRelationsAndFields($settings);
            $repository->getTrainingVideoData($settings);
            return make_response($request, $settings, 200, ['data' => $settings]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return make_response($request, null, 500,null);
        }
    }

    public function storePosSetting(Request $request, Creator $creator)
    {
        try {
            $partner = resolvePartnerFromAuthMiddleware($request);
            $partnerPosSetting = PartnerPosSetting::where('partner_id', $partner->id)->first();
            if (!$partnerPosSetting) $partnerPosSetting = $creator->setData(['partner_id' => $partner->id])->create();
            $data = [];
            $this->setModifier(resolveManagerResourceFromAuthMiddleware($request));

            if ($request->has('vat_percentage')) $data["vat_percentage"] = $request->vat_percentage;
            if ($request->has('sms_invoice')) $data["sms_invoice"] = $request->sms_invoice;
            if ($request->has('auto_printing')) $data["auto_printing"] = $request->auto_printing;
            if ($request->has('printer_name')) $data["printer_name"] = $request->printer_name;
            if ($request->has('printer_model')) $data["printer_model"] = $request->printer_model;

            $partnerPosSetting->update($this->withUpdateModificationField($data));
            return make_response($request, null, 200,['message' => 'Successful']);
        } catch (Throwable $e) {
            logError($e);
            app('sentry')->captureException($e);
            return make_response($request, null, 500,null);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InsufficientBalanceException
     */
    public function duePaymentRequestSms(Request $request)
    {
        $this->validate($request, ['customer_id' => 'required|numeric', 'due_amount' => 'required']);
        $partner = $request->partner;
        $this->setModifier($request->manager_resource);
        $customer = PosCustomer::find($request->customer_id);
        $sms = (new SmsHandlerRepo('due-payment-collect-request'))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::POS)
            ->setMessage([
                'partner_name' => $partner->name,
                'due_amount' => $request->due_amount
            ])
            ->setMobile($customer->profile->mobile);
        $sms_cost = $sms->estimateCharge();
        //freeze money amount check
        WalletTransactionHandler::isDebitTransactionAllowed($request->partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        if ((double)$partner->wallet < $sms_cost) throw new InsufficientBalanceException();
        $sms->send($customer->profile->mobile, [
            'partner_name' => $partner->name,
            'due_amount' => $request->due_amount
        ]);
        $log = $sms_cost . " BDT has been deducted for sending due payment request sms";
        (new WalletTransactionHandler())->setModel($request->partner)->setAmount($sms_cost)->setType(Types::debit())->setLog($log)->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();

        return api_response($request, null, 200, ['msg' => 'SMS Send Successfully']);
    }
}
