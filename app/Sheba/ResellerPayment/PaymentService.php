<?php namespace App\Sheba\ResellerPayment;

use App\Models\Partner;
use Sheba\Dal\DigitalCollectionSetting\Model as DigitalCollectionSetting;
use Sheba\Dal\PgwStore\Model as PgwStore;
use Sheba\Dal\PgwStoreAccount\Model as PgwStoreAccount;
use Sheba\Dal\Survey\Model as Survey;
use Sheba\EMI\Banks;
use Sheba\EMI\CalculatorForManager;
use Sheba\MerchantEnrollment\MerchantEnrollment;
use Sheba\ModificationFields;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\PaymentLink\PaymentLinkStatus;

class PaymentService
{
    use ModificationFields;

    private $partner;
    private $status;
    private $mefStatus;
    private $surveyStatus;
    private $eKycStatus;
    private $pgwStatus;

    /**
     * @param mixed $partner
     * @return PaymentService
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return array
     */
    public function getStatusAndBanner()
    {
        $this->getResellerPaymentStatus();
        $this->getPgwStatus();

        return [
            'status' => $this->status ?? null,
            'pgw_status' => $this->pgwStatus ?? null,
            'banner' => $this->getBanner()
        ];
    }

    private function getBanner()
    {

    }

    private function getResellerPaymentStatus()
    {
       $this->getMefStatus();
       if(isset($this->status))
           return;
       $this->getSurveyStatus();
       if(isset($this->status))
           return;
       $this->getEkycStatus();

    }

    private function getMefStatus()
    {
        return;
    }

    private function getSurveyStatus()
    {
        $survey =  Survey::where('user_type',get_class($this->partner))->where('user_id', $this->partner->id)->first();
        if($survey)
            $this->status = 'survey_completed';
    }

    private function getEkycStatus()
    {
        if($this->partner->isNIDVerified())
            $this->status = 'ekyc_completed';
    }

    private function getPgwStatus()
    {
        $pgw_store_accounts = PgwStoreAccount::where('user_type',get_class($this->partner))->where('user_id', $this->partner->id)->first();
        if($pgw_store_accounts)
            $this->pgwStatus = $pgw_store_accounts->staus;
    }

    /**
     * @param $completion
     * @param $header_message
     * @param $partnerId
     * @return array
     * @throws \Sheba\ResellerPayment\Exceptions\InvalidKeyException
     */
    public function getPaymentGateways($completion, $header_message, $partnerId): array
    {
        $pgwData = [];
        $partner = Partner::where('id', $partnerId)->first();
        $partner_account = $partner->pgwStoreAccounts()->select('status')->first();
        $pgwStores = new PgwStore();

        $pgwStores = $pgwStores->select('id', 'name', 'key', 'name_bn', 'icon')->get();
        foreach ($pgwStores as $pgwStore) {
            $completionData = (new MerchantEnrollment())->setPartner($partner)->setKey($pgwStore->key)->getCompletion();
            if (!$partner_account) {
                $pgwData[] = [
                    'id' => $pgwStore->id,
                    'name' => $pgwStore->name,
                    'key' => $pgwStore->key,
                    'name_bn' => $pgwStore->name_bn,
                    'header' => $pgwStore->key === 'ssl' ? $header_message : null,
                    'completion' => $completion == 1 ? $completionData->getOverallCompletion()['en'] : null,
                    'icon' => $pgwStore->icon,
                    'status' => PaymentLinkStatus::UNREGISTERED
                ];
            } else if ($partner_account->status == 1) {
                $pgwData[] = [
                    'id' => $pgwStore->id,
                    'name' => $pgwStore->name,
                    'key' => $pgwStore->key,
                    'name_bn' => $pgwStore->name_bn,
                    'header' => $pgwStore->key === 'ssl' ? $header_message : null,
                    'completion' => $completion == 1 ? $completionData->getOverallCompletion()['en'] : null,
                    'icon' => $pgwStore->icon,
                    'status' => PaymentLinkStatus::ACTIVE
                ];
            } else {
                $pgwData[] = [
                    'id' => $pgwStore->id,
                    'name' => $pgwStore->name,
                    'key' => $pgwStore->key,
                    'name_bn' => $pgwStore->name_bn,
                    'header' => $pgwStore->key === 'ssl' ? $header_message : null,
                    'completion' => $completion == 1 ? $completionData->getOverallCompletion()['en'] : null,
                    'icon' => $pgwStore->icon,
                    'status' => PaymentLinkStatus::INACTIVE
                ];
            }
        }
        return $pgwData;
    }

    /**
     * @param $partnerId
     * @return array
     */
    public function getServiceCharge($partnerId): array
    {
        $digitalCollection = DigitalCollectionSetting::where('partner_id', $partnerId)->select('service_charge')->first();

        $data = PaymentLinkStatics::customPaymentServiceData();
        if ($digitalCollection) {
            $data['current_percentage'] = $digitalCollection->service_charge;
        } else {
            $data['current_percentage'] = PaymentLinkStatics::SERVICE_CHARGE;
        }
        return $data;
    }

    /**
     * @param $partnerId
     * @param $currentPercentage
     */
    public function storeServiceCharge($partnerId, $currentPercentage)
    {
        $digitalCollectionSetting = new DigitalCollectionSetting();
        $partner = $digitalCollectionSetting->where('partner_id', $partnerId)->first();

        if (!$partner) {
            $digitalCollectionSetting->partner_id = $partnerId;
            $digitalCollectionSetting->service_charge = $currentPercentage;
            $this->withCreateModificationField($digitalCollectionSetting);
            $digitalCollectionSetting->save();
        } else {
            $data = ['service_charge' => $currentPercentage];
            $digitalCollectionSetting->query()->where('partner_id', $partnerId)
                ->update($this->withUpdateModificationField($data));
        }
    }

    /**
     * @param $partner
     * @param $amount
     * @return array
     */
    public function getEmiInfoForManager($partner, $amount): array
    {
        $emi_calculator  = new CalculatorForManager();
        $icons_folder = getEmiBankIconsFolder(true);
        return [
            "emi"   => $emi_calculator->setPartner($partner)->getCharges($amount),
            "banks" => (new Banks())->setAmount($amount)->get(),
            "minimum_amount" => number_format(config('sheba.min_order_amount_for_emi')),
            "static_info"    => [
                "how_emi_works"        => [
                    "EMI (Equated Monthly Installment) is one of the payment methods of online purchasing, only for the customers using any of the accepted Credit Cards on Sheba.xyz.* It allows customers to pay for their ordered services  in easy equal monthly installments.*",
                    "Sheba.xyz has introduced a convenient option of choosing up to 12 months EMI facility for customers who use Credit Cards for buying services worth BDT 5,000 or more. The duration and extent of the EMI options available will be visible on the payment page after order placement. EMI plans are also viewable on the checkout page in the EMI Banner below the bill section.",
                    "Customers wanting to avail EMI facility must have a Credit Card from any one of the banks in the list shown in the payment page.",
                    "EMI facilities available for all services worth BDT 5,000 or more.",
                    "EMI charges may vary on promotional offers.",
                    "Sheba.xyz  may charge additional convenience fee if the customer extends the period of EMI offered."
                ],
                "terms_and_conditions" => [
                    "As soon as you complete your purchase order on Sheba.xyz, you will see the full amount charged on your credit card.",
                    "You must Sign and Complete the EMI form and submit it at Sheba.xyz within 3 working days.",
                    "Once Sheba.xyz receives this signed document from the customer, then it shall be submitted to the concerned bank to commence the EMI process.",
                    "The EMI processing will be handled by the bank itself *. After 5-7 working days, your bank will convert this into EMI.",
                    "From your next billing cycle, you will be charged the EMI amount and your credit limit will be reduced by the outstanding amount.",
                    "If you do not receive an updated monthly bank statement reflecting your EMI transactions for the following month, feel free to contact us at 16516  for further assistance.",
                    "For example, if you have made a 3-month EMI purchase of BDT 30,000 and your credit limit is BDT 1, 00,000 then your bank will block your credit limit by BDT 30,000 and thus your available credit limit after the purchase will only be BDT 70,000. As and when you pay your EMI every month, your credit limit will be released accordingly.",
                    "EMI facilities with the aforesaid Banks are regulated as per their terms and conditions and these terms may vary from one bank to another.",
                    "For any query or concern please contact your issuing bank, if your purchase has not been converted to EMI by 7 working days of your transaction date."
                ]
            ]
        ];
    }
}