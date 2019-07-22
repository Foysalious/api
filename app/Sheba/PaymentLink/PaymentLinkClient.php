<?php namespace Sheba\PaymentLink;

use App\Sheba\Payment\Exceptions\PayableNotFound;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class PaymentLinkClient
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

    public function paymentLinkList(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;

            $url = "$this->baseUrl?userType=$user_type&userId=$user_id";
            $response = $this->client->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['links'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function defaultPaymentLink(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;

            $url = "$this->baseUrl?userType=$user_type&userId=$user_id&isDefault=1";
            $response = $this->client->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['links'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function storePaymentLink($data)
    {
        try {
            $response = $this->client->request('POST', $this->baseUrl, ['form_params' => $data]);
            $response = json_decode($response->getBody());
            if ($response->code == 200)
                return $response->link;
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function paymentLinkStatusChange($link, $data)
    {
        try {
            if ($data['status'] == 'active') {
                $status = 1;
            } else {
                $status = 0;
            }

            $url = $this->baseUrl . '/' . $link . '?isActive=' . $status;
            $response = $this->client->request('PUT', $url, []);
            $response = json_decode($response->getBody());
            if ($response->code == 200)
                return $response;
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function paymentLinkDetails($link)
    {
        try {
            $url = $this->baseUrl . '/' . $link;
            $response = $this->client->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['link'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getPaymentLinkDetails($userId, $userType, $identifier)
    {
        $url = $this->baseUrl . '?userId=' . $userId . '&userType=' . $userType . '&linkIdentifier=' . $identifier;
        $response = $this->client->get($url)->getBody()->getContents();
        $result = json_decode($response, true);
        if ($result['code'] == 200) {
            return $result['links'][0];
        } else {
            throw new PayableNotFound();
        }
    }
}