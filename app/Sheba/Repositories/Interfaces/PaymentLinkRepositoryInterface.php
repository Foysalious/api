<?php namespace Sheba\Repositories\Interfaces;


use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\PaymentLink\Target;
use Sheba\PaymentLink\UrlTransformer;

interface PaymentLinkRepositoryInterface extends BaseRepositoryInterface
{
    public function statusUpdate($id, $status);

    public function paymentLinkDetails($id);

    /**
     * @param $identifier
     * @return PaymentLinkTransformer|null
     */
    public function findByIdentifier($identifier);

    /**
     * @param $url
     * @return UrlTransformer
     */
    public function createShortUrl($url);

    /**
     * @param array $attributes
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(array $attributes);

    /**
     * @param $id
     * @return PaymentLinkTransformer|null
     */
    public function find($id);

    /**
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getPaymentLinkByTargetIdType($id, $type);

    /**
     * @param $targets Target[]
     * @return PaymentLinkTransformer[][]
     */
    public function getPaymentLinksGroupedByTargets(array $targets);

    /**
     * @param $targets Target[]
     * @return PaymentLinkTransformer[][]
     */
    public function getPaymentLinksByPosOrders(array $targets);
}
