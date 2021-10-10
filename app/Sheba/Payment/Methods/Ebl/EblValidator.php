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
     * @param bool $changeStatus
     * @return mixed
     * @throws EblServerException
     */
    public function validate(Payment $payment, $changeStatus = true)
    {
        try {
            $payload=json_decode($payment->request_payload, true);
            $resp     = $this->client->post(config('sheba.ebl_url') . '/validate', ['form_params' => $payload])->getBody()->getContents();
            $response = json_decode($resp, true);
            if ($changeStatus) {
                if ($response['data']['success'] && $payload['decision']=='ACCEPT') {
                    $this->statusChanger->setPayment($payment)->changeToValidated($resp);
                } else {
                    $this->statusChanger->setPayment($payment)->changeToValidationFailed($resp);
                }
            }
            return $response;
        } catch (\Throwable $e) {
            $this->statusChanger->setPayment($payment)->changeToValidationFailed($e->getMessage());
            logError($e);
            throw new EblServerException($e->getMessage());
        }
    }
}
