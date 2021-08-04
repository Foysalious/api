<?php namespace Sheba\PaymentLink;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\PayableNotFound;
use Exception;

class PaymentLinkClient
{
    /** @var string */
    private $baseUrl;
    private $partnerPaymentUrl;
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->baseUrl = config('sheba.payment_link_url') . '/api/v1/payment-links';
        $this->partnerPaymentUrl = config('sheba.payment_link_url') . '/api/v1/partner-payment-links';
        $this->client = $client;
    }

    public function paymentLinkList(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;
            $search_value = $request->search;
            $limit = $request->limit;
            $offset = $request->offset;
            $order = $request->order;
            $linkType = $request->linkType;

            $url = "$this->baseUrl?userType=$user_type&userId=$user_id&search=$search_value&limit=$limit&offset=$offset&order=$order&linkType=$linkType";
            $response = $this->client->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['links'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function partnerPaymentLinkList(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;
            $search_value = $request->search;
            $limit = $request->limit;
            $offset = $request->offset;
            $order = $request->order;
            $linkType = $request->linkType;
            $paymentLinkType = $request->payment_link_type;
            $url = "$this->partnerPaymentUrl?userType=$user_type&userId=$user_id&search=$search_value&limit=$limit&offset=$offset&order=$order&linkType=$linkType&paymentLinkType=$paymentLinkType";

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
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function storePaymentLink($data)
    {
        $response = $this->client->request('POST', $this->baseUrl, ['form_params' => $data]);
        $response = json_decode($response->getBody());
        if ($response->code == 200)
            return $response->link;
        throw new Exception(json_encode($response));
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
     * @param $targets Target[]
     * @return array
     */
    public function getPaymentLinksByTargets(array $targets)
    {
        if (empty($targets)) return [];

        $targets = array_map(function (Target $target) {
            return [
                "targetType" => $target->getType(),
                "targetId" => $target->getId(),
            ];
        }, $targets);

        $uri = $this->baseUrl . '?targets=' . json_encode($targets);
        $response = json_decode($this->client->get($uri)->getBody()->getContents(), true);

        if ($response['code'] != 200) return [];

        return $response['links'];
    }

    /**
     * @param $targets Target[]
     * @return array
     */
    public function getPaymentLinksByPosOrders(array $targets)
    {
        $targets = array_filter(array_map(function (Target $target) {
            if ($target->getType() != TargetType::POS_ORDER) return null;
            return $target->getId();
        }, $targets));

        if (empty($targets)) return [];

        $uri = $this->baseUrl . '?posOrders=' . implode(",", $targets);

        $response = json_decode($this->client->get($uri)->getBody()->getContents(), true);

        if ($response['code'] != 200) return [];

        return $response['links'];
    }

    public function getActivePaymentLinksByPosOrders(array $targets)
    {
        $targets = array_filter(array_map(function (Target $target) {
            if ($target->getType() != TargetType::POS_ORDER) return null;
            return $target->getId();
        }, $targets));

        if (empty($targets)) return [];

        $uri = $this->baseUrl . '?posOrders=' . implode(",", $targets) . '&isActive=' . 1;

        $response = json_decode($this->client->get($uri)->getBody()->getContents(), true);

        if ($response['code'] != 200) return [];

        return $response['links'];
    }

    public function getActivePaymentLinkByPosOrder($target)
    {
       return $this->getActivePaymentLinksByPosOrders([$target]);
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
