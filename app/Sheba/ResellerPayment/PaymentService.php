<?php namespace App\Sheba\ResellerPayment;

use Sheba\Dal\PgwStoreAccount\Model as PgwStoreAccount;
use Sheba\Dal\Survey\Model as Survey;
use Sheba\MerchantEnrollment\MerchantEnrollment;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;

class PaymentService
{
    private $partner;
    private $status;
    private $pgwStatus;
    private $key;
    private $rejectReason;
    private $pgwMerchantId;

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
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getPGWDetails()
    {
        $this->getResellerPaymentStatus();
        $this->getPgwStatus();
        return [
            'banner' =>'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/reseller_payment/payment_gateway_banner/app-banner+(1)+2.png',
            'faq' => [
                'আপনার ব্যবসার প্রোফাইল সম্পন্ন করুন',
                'পেমেন্ট সার্ভিসের জন্য আবেদন করুন',
                'পেমেন্ট সার্ভিস কনফিগার করুন'
            ],
            'status' => $this->status ?? null,
            'mor_status_wise_disclaimer' => in_array($this->status,['pending','processing','verified','rejected']) ? config('reseller_payment.mor_status_wise_text')[$this->key][$this->status] : null,
            'pgw_status' =>  $this->pgwStatus ?? null,
            'pgw_merchant_id' => $this->pgwMerchantId,
            'how_to_use_link' => 'https://partners.dev-sheba.xyz/api/how-to-use',
            'payment_service_info_link' => 'https://partners.dev-sheba.xyz/api/payment-setup-faq'
        ];

    }

    public function getStatusAndBanner()
    {
        $this->getResellerPaymentStatus();
        $this->getPgwStatus();

        return [
            'status' => $this->status ?? null,
            'pgw_status' => $this->pgwStatus ?? null,
            'banner' => $this->getBanner(),
            'webview_link' => 'https://partners.dev-sheba.xyz/api/payment-link-faq'
        ];
    }

    private function getBanner()
    {
        $banner = null;
        if($this->pgwStatus == 0)
            $banner = config('reseller_payment.status_wise_home_banner')['pgw_inactive'];
        elseif ($this->status == 'verified')
            $banner = config('reseller_payment.status_wise_home_banner')['verified'];
       elseif($this->status == 'rejected')
           $banner = config('reseller_payment.status_wise_home_banner')['rejected'];
       elseif ($this->status == 'ekyc_completed')
           $banner = config('reseller_payment.status_wise_home_banner')['ekyc_completed'];
       elseif ($this->status == 'mef_completed' || $this->status == 'survey_completed')
           $banner = config('reseller_payment.status_wise_home_banner')['completed_but_did_not_apply'];
      elseif(is_null($this->status))
          $banner = config('reseller_payment.status_wise_home_banner')['did_not_started_journey'];

      return $banner;

    }

    private function getResellerPaymentStatus()
    {
        $this->getMORStatus();
        if(isset($this->status))
            return;
        $this->checkMefCompletion();
        if(isset($this->status))
            return;
        $this->getSurveyStatus();
        if(isset($this->status))
            return;
        $this->getEkycStatus();

    }

    private function getMORStatus()
    {
        return null;
        /** @var MORServiceClient $morClient */
        $morClient = app(MORServiceClient::class);
        $morResponse = $morClient->get('api/v1/client/applications/status?user_id='.$this->partner->id.'&user_type=partner');
        if($morResponse){
            $this->status = $morStatus = $morResponse['application_status'];
            if($morStatus == 'rejected')
                $this->rejectReason = $morResponse['reject_reason'];
            return $morStatus;
        }
        return null;

    }

    private function checkMefCompletion()
    {
        /** @var MerchantEnrollment $merchantEnrollment */
        $merchantEnrollment = app(MerchantEnrollment::class);
        $completion = $merchantEnrollment->setPartner($this->partner)->setKey($this->key)->getCompletion()->toArray();
        if($completion['can_apply'] == 1)
            $this->status = 'mef_completed';
       return true;
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
        $pgw_store_account = PgwStoreAccount::where('user_type',get_class($this->partner))->where('user_id', $this->partner->id)->first();
        if($pgw_store_account)
        {
            $this->pgwStatus = $pgw_store_account->status;
            $this->pgwMerchantId = (new DynamicSslStoreConfiguration($pgw_store_account->configuration))->getStoreId();
        }






    }

}