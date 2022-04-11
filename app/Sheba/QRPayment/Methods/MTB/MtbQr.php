<?php

namespace App\Sheba\QRPayment\Methods\MTB;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\Exceptions\MtbServiceServerError;
use App\Sheba\MTB\MtbServerClient;
use App\Sheba\QRPayment\Methods\QRPaymentMethod;
use App\Sheba\QRPayment\QRPaymentStatics;
use Carbon\Carbon;
use Sheba\QRPayment\Exceptions\QRException;

class MtbQr extends QRPaymentMethod
{
    private $mtbClient;

    private $qrString;

    private $refId;

    public function __construct(MtbServerClient $client)
    {
        $this->mtbClient = $client;
    }

    /**
     * @return bool
     * @throws NotFoundAndDoNotReportException
     * @throws MtbServiceServerError
     */
    public function validate(): bool
    {
        $data = $this->makeApiData();

        $url = QRPaymentStatics::MTB_VALIDATE_URL . http_build_query($data);

        $response = $this->mtbClient->get($url, AuthTypes::BASIC_AUTH_TYPE);

        if(isset($response["transactions"])) {
            $transaction = $response["transactions"];
            if(count($transaction) > 0) return true;
        }
        return false;
    }

    private function makeApiData(): array
    {
        return array(
            'mid'   => $this->merchantId,
            'amt'   => $this->amount,
            'txndt' => Carbon::now()->format("Y-m-d")
        );
    }

    /**
     * @throws MtbServiceServerError
     * @throws NotFoundAndDoNotReportException|QRException
     */
    public function getMTBQRString(): MtbQr
    {
        $data = $this->makeDataForQRGenerate();

        $url = QRPaymentStatics::MTB_QR_GENERATE_URL . http_build_query($data);

        $response = $this->mtbClient->get($url, AuthTypes::BASIC_AUTH_TYPE);

        if(isset($response["respCode"])) {
            if($response["respCode"] === "001") {
                $this->qrString = base64_decode(strrev($response["QRString"]));
                $this->refId = $response["RefNo"];
                return $this;
            }
        }

        throw new QRException("Qr generation failed");
    }

    private function makeDataForQRGenerate(): array
    {
        return array(
            'mid'    => $this->merchantId,
            'amount' => $this->amount
        );
    }

    /**
     * @return mixed
     */
    public function getQrString()
    {
        return $this->qrString;
    }

    /**
     * @return mixed
     */
    public function getRefId()
    {
        return $this->refId;
    }
}