<?php namespace Sheba\Repositories;


use App\Sheba\Payment\Exceptions\PayableNotFound;
use GuzzleHttp\Client;

class PaymentLinkRepository
{
    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->baseUrl = config('sheba.payment_link_url') . '/api/v1/payment-links';
        $this->client = new Client();
    }

    public function getPaymentLinkDetails($userId, $userType, $identifier)
    {
        $url = $this->baseUrl . '?userId=' . $userId . '&userType=' . $userType . '&linkIdentifier=' . $identifier;
        $response = $this->client->get($url)->getBody()->getContents();
        $result = json_decode($response, true);
        if ($result['code'] == 200) {
            return $result['links'];
        } else {
            throw new PayableNotFound();
        }
    }
}
