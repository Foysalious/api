<?php namespace Sheba\PaymentLink;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\PayableNotFound;

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
            $search_value = $request->search;

            $url = "$this->baseUrl?userType=$user_type&userId=$user_id&search=$search_value";
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

    /**
     * @param $data
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storePaymentLink($data)
    {
        try {
            $response = $this->client->request('POST', $this->baseUrl, ['form_params' => $data]);
            $response = json_decode($response->getBody());
            if ($response->code == 200)
                return $response->link;
            return null;
        } catch (\Throwable $e) {
            dd($e);
            return null;
        }
    }

    public function paymentLinkStatusChange($link, $status)
    {
        try {
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

    /**
     * @param $userId
     * @param $userType
     * @param $identifier
     * @return mixed
     * @throws PayableNotFound
     */
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

    /**
     * @param $linkId
     * @return mixed
     */
    public function getPaymentLinkByLinkId($linkId)
    {
        $url = $this->baseUrl . '?linkId=' . $linkId;
        $response = $this->client->get($url)->getBody()->getContents();
        return json_decode($response, true);
    }

    /**
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getPaymentLinkByTargetIdType($id, $type)
    {
        $uri = $this->baseUrl . '?targetId=' . $id . '&targetType=' . $type;
        $response = $this->client->get($uri)->getBody()->getContents();
        return json_decode($response, true);
    }

    /**
     * @param $identifier
     * @return \stdClass|null
     */
    public function getPaymentLinkByIdentifier($identifier)
    {
        $url = $this->baseUrl . '?linkIdentifier=' . $identifier;
        $response = $this->client->get($url)->getBody()->getContents();
        $result = json_decode($response, true);
        if ($result['code'] == 200) {
            return $result['links'][0];
        } else {
            return null;
        }
    }

    public function createShortUrl($url)
    {
        try {
            $response = $this->client->request('POST', config('sheba.payment_link_url') . '/api/v1/urls', ['form_params' => ['originalUrl' => $url]]);
            return json_decode($response->getBody());
        } catch (\Throwable $e) {
            return null;
        }
    }
}
