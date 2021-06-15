<?php


namespace Sheba\Payment\Methods\Ebl;


use App\Models\Payment;
use GuzzleHttp\Client;
use Sheba\Payment\Methods\Ebl\Exception\EblServerException;
use Sheba\Payment\StatusChanger;

class EblValidator
{
    /**
     * @var StatusChanger
     */
    private $statusChanger;
    /**
     * @var Client
     */
    private $client;

    public function __construct(StatusChanger $statusChanger, Client $client)
    {
        $this->statusChanger = $statusChanger;
        $this->client        = $client;
    }

    /**
     * @param Payment $payment
     * @throws EblServerException
     */
    public function validate(Payment $payment)
    {
        try {
            $resp     = $this->client->post(config('sheba.ebl_url') . '/validate', json_decode($payment->request_payload, true))->getBody()->getContents();
            dd($resp);
            $response = json_decode($resp, true);
            if ($response['data']['status']) {
                $this->statusChanger->setPayment($payment)->changeToValidated($payment->request_payload);
            } else {
                $this->statusChanger->setPayment($payment)->changeToValidationFailed($resp);
            }
        } catch (\Throwable $e) {
            $this->statusChanger->setPayment($payment)->changeToValidationFailed($e->getMessage());
            logError($e);
            throw new EblServerException($e->getMessage());
        }
    }
}
