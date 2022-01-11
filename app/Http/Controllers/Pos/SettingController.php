<?php namespace App\Http\Controllers\Pos;

use App\Exceptions\Pos\SMS\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPosSetting;
use App\Models\PosCustomer;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\InventoryService\Services\PartnerService;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Exception;
use Sheba\Pos\Customer\PosCustomerResolver;
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
    public function getSettings(Request $request, Creator $creator, PosSettingRepository $repository,PartnerService $partnerService, OrderService $orderService)
    {
        $settings = $this->getSettingsData($request,$creator,$repository,$partnerService,$orderService);
        if(!$settings) return api_response($request, null, 500,null);
        else return api_response($request, $settings,200, ['settings' => $settings]);
    }

    public function getSettingsV2(Request $request, Creator $creator, PosSettingRepository $repository, PartnerService $partnerService, OrderService $orderService)
    {
        $settings = $this->getSettingsData($request,$creator,$repository,$partnerService,$orderService);
        if(!$settings) return http_response($request, null, 500,null);
        else return http_response($request, $settings,200, ['settings' => $settings]);
    }

    private function getSettingsData(Request $request, Creator $creator, PosSettingRepository $repository,PartnerService $partnerService, OrderService $orderService)
    {
        try {
            $partner = resolvePartnerFromAuthMiddleware($request);
            $settings = PartnerPosSetting::byPartner($partner->id)->select('id', 'partner_id', 'vat_percentage', 'auto_printing', 'sms_invoice', 'printer_name', 'printer_model')->first();
            if (!$settings) {
                $data = ['partner_id' => $partner->id];
                $creator->setData($data)->create();
                $settings = PartnerPosSetting::byPartner($partner->id)->select('id', 'partner_id', 'vat_percentage', 'auto_printing', 'sms_invoice')->first();
            }
            $settings->vat_registration_number = $partner->basicInformations->vat_registration_number;
            $settings->show_vat_registration_number = $partner->basicInformations->show_vat_registration_number;
            $settings['has_qr_code'] = ($partner->qr_code_image && $partner->qr_code_account_type) ? 1 : 0;
            if($partner->isMigrated(Modules::POS))
            {
                $data = ['partner_id' => $partner->id, 'sub_domain' => $partner->sub_domain, 'vat_percentage' => $settings->vat_percentage];
                $settings->vat_percentage = $partnerService->setPartner($partner)->storeOrGet($data)['partner']['vat_percentage'];
                $pos_order_settings_data = [
                    'partner_id' => $partner->id,
                    'name' => $partner->name,
                    'sub_domain' => $partner->sub_domain,
                    'sms_invoice' => $settings->sms_invoice,
                    'auto_printing' => $settings->auto_printing,
                    'printer_name' => $settings->printer_name,
                    'printer_model' => $settings->printer_model,
                    'delivery_charge' => $partner->delivery_charge,
                    'qr_code_account_type' => $partner->qr_code_account_type,
                    'qr_code_image' => $partner->qr_code_image
                ];
                $partnerDetailsFromOderService = $orderService->setPartnerId($partner->id)->storeOrGet($pos_order_settings_data);
                $settings->has_qr_code = $partnerDetailsFromOderService['partner']['qr_code_account_type'] && $partnerDetailsFromOderService['partner']['qr_code_image'] ?  1 : 0;
            }

            removeRelationsAndFields($settings);
            return $settings;
        } catch (Throwable $e) {
            logError($e);
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function getPrinterSettings(Request $request, Creator $creator, PosSettingRepository $repository)
    {
        $printer_settings_data = $this->getPrinterSettingsData($request,$creator,$repository);
        if($printer_settings_data)
            return api_response($request, $printer_settings_data, 200, ['data' => $printer_settings_data]);
        else
            return api_response($request, null, 500);

    }

    public function getPrinterSettingsV2(Request $request, Creator $creator, PosSettingRepository $repository)
    {
        $printer_settings_data = $this->getPrinterSettingsData($request,$creator,$repository);
        if($printer_settings_data)
            return http_response($request, $printer_settings_data, 200, ['data' => $printer_settings_data]);
        else
            return http_response($request, null, 500);

    }

    public function storePosSetting(Request $request, Creator $creator,PartnerService $partnerService)
    {
        $settings_saved = $this->savePosSettings($request,$creator,$partnerService);
        if(!$settings_saved) return api_response($request, null, 500);
        else return api_response($request, null,200, ['message' => 'Successful']);
    }

    public function storePosSettingV2(Request $request, Creator $creator, PartnerService $partnerService)
    {
        $settings_saved = $this->savePosSettings($request,$creator,$partnerService);
        if(!$settings_saved) return http_response($request, null, 500);
        else return http_response($request, null,200);
    }

    private function savePosSettings(Request $request, Creator $creator, PartnerService $partnerService)
    {
        try {
            $partner = resolvePartnerFromAuthMiddleware($request);
            $partnerPosSetting = PartnerPosSetting::where('partner_id', $partner->id)->first();
            if (!$partnerPosSetting) $partnerPosSetting = $creator->setData(['partner_id' => $partner->id])->create();
            $data = [];
            $this->setModifier(resolveManagerResourceFromAuthMiddleware($request));

            if ($request->filled('vat_percentage') && !$partner->isMigrated(Modules::POS)) $data["vat_percentage"] = $request->vat_percentage;
            if ($request->filled('sms_invoice')) $data["sms_invoice"] = $request->sms_invoice;
            if ($request->filled('auto_printing')) $data["auto_printing"] = $request->auto_printing;
            if ($request->filled('printer_name')) $data["printer_name"] = $request->printer_name;
            if ($request->filled('printer_model')) $data["printer_model"] = $request->printer_model;

            $partnerPosSetting->update($this->withUpdateModificationField($data));

            if($request->filled('vat_percentage') && $partner->isMigrated(Modules::POS)){
                $partnerService->setPartner($partner)->setVatPercentage($request->vat_percentage)->update();
            }
            return true;
        } catch (Throwable $e) {
            logError($e);
            app('sentry')->captureException($e);
            return false;
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InsufficientBalanceException
     * @throws Exception
     */
    public function duePaymentRequestSms(Request $request, PosCustomerResolver $posCustomerResolver)
    {
        $this->validate($request, ['customer_id' => 'required', 'due_amount' => 'required']);
        /** @var Partner $partner */
        $partner = $request->partner;
        $this->setModifier($request->manager_resource);
        $customer =$posCustomerResolver->setCustomerId($request->customer_id)->setPartner($partner)->get();
        $variables=[
            'partner_name' => $partner->name,
            'due_amount' => $request->due_amount,
            'company_number'=>$partner->getContactNumber()
        ];
        $sms = (new SmsHandlerRepo('due-payment-collect-request'))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::POS)
            ->setMessage($variables)
            ->setMobile($customer->mobile);
        $sms_cost = $sms->estimateCharge();
        //freeze money amount check
        WalletTransactionHandler::isDebitTransactionAllowed($partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        if ((double)$partner->wallet < $sms_cost) throw new InsufficientBalanceException();
        $sms->send($customer->profile->mobile, [
            'partner_name' => $partner->name,
            'due_amount' => $request->due_amount
        ]);
        $log = $sms_cost . " BDT has been deducted for sending due payment request sms";
        (new WalletTransactionHandler())->setModel($request->partner)->setAmount($sms_cost)->setType(Types::debit())->setLog($log)->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();

        return api_response($request, null, 200, ['msg' => 'SMS Send Successfully']);
    }

    private function getPrinterSettingsData(Request $request, Creator $creator, PosSettingRepository $repository)
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
            return $settings;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}
