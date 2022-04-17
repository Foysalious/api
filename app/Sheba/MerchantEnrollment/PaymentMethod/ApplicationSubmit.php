<?php

namespace App\Sheba\MerchantEnrollment\PaymentMethod;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Models\Partner;
use App\Sheba\MerchantEnrollment\PersonalInformation;
use App\Sheba\ResellerPayment\Exceptions\MORServiceServerError;
use App\Sheba\ResellerPayment\MORServiceClient;
use Sheba\Dal\PgwStore\Model as PGWStore;
use Sheba\MerchantEnrollment\Exceptions\IncompleteSubmitData;
use Sheba\MerchantEnrollment\Exceptions\InvalidListInsertionException;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\MerchantEnrollment\MEFFormCategoryFactory;
use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class ApplicationSubmit
{
    /*** @var Partner */
    private $partner;

    /*** @var PGWStore */
    private $payment_gateway;

    private $partner_data;
    private $data;
    private $partner_survey_data;

    /**
     * @param mixed $partner
     * @return ApplicationSubmit
     */
    public function setPartner($partner): ApplicationSubmit
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $payment_gateway
     * @return ApplicationSubmit
     */
    public function setPaymentGateway($payment_gateway): ApplicationSubmit
    {
        $this->payment_gateway = $payment_gateway;
        return $this;
    }

    /**
     * @return void
     * @throws IncompleteSubmitData
     * @throws InvalidKeyException
     * @throws InvalidListInsertionException
     * @throws MORServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    public function submit()
    {
        $this->validate();
        $this->makeData();
        $this->store();
    }

    /**
     * @return void
     * @throws NotFoundAndDoNotReportException
     * @throws MORServiceServerError
     */
    private function store()
    {
        /** @var MORServiceClient $morClient */
        $morClient = app(MORServiceClient::class);
        $morClient->post("api/v1/clients/applications/store", $this->data);
    }

    /**
     * @return void
     * @throws IncompleteSubmitData
     * @throws InvalidKeyException
     * @throws InvalidListInsertionException
     */
    private function validate()
    {
        $this->validateNID();
        $this->validateData();
        $this->validateSurvey();
    }

    /**
     * @return void
     * @throws IncompleteSubmitData
     */
    private function validateSurvey()
    {
        if($this->partner->survey->first()) {
            $this->partner_survey_data = $this->partner->survey->first()->result;
            return;
        }
        throw new IncompleteSubmitData("Survey data not found");
    }

    /**
     * @return void
     * @throws IncompleteSubmitData
     */
    private function validateNID()
    {
        if((new PersonalInformation())->setPartner($this->partner)->isNidVerified()) return;
        throw new IncompleteSubmitData("Nid is not verified");
    }

    /**
     * @return void
     * @throws IncompleteSubmitData
     * @throws InvalidKeyException
     * @throws InvalidListInsertionException
     */
    private function validateData()
    {
        $list = (new MEFFormCategoryFactory())->setPaymentGateway($this->payment_gateway)->setPartner($this->partner)->getAllCategory();
        $iterator   = $list->getIterator();
        $all_data = [];
        while ($iterator->valid()) {
            /** @var MEFFormCategory $current */
            $current      = $iterator->current();
            $all_data[] = $current->getFormFieldData();
            $iterator->next();
        }
        foreach ($all_data as $category_data)
            foreach ($category_data as $key => $value) {
                if (isset($value)) $this->partner_data[$key] = $value;
                else throw new IncompleteSubmitData("Incomplete Data. " . $key . " is empty");
            }
    }

    /**
     * @return void
     */
    private function makeData()
    {
        $other_data = (new PersonalInformation())->setPartner($this->partner)->getPersonalPhoto();
        $this->partner_data = array_merge($this->partner_data, $other_data);

        $this->data = [
            "application_data" => json_encode($this->partner_data),
            "user_type"        => MEFGeneralStatics::USER_TYPE_PARTNER,
            "user_id"          => $this->partner->id,
            "pgw_store_key"    => $this->payment_gateway->key,
            "survey_data"      => $this->partner_survey_data,
            "request_type"     => $this->partner_data["monthly_transaction_amount"] < 100000 ? "PRA" : "Regular"
        ];
    }
}
