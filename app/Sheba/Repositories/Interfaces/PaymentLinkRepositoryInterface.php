<?php namespace Sheba\Repositories\Interfaces;


use Sheba\PaymentLink\PaymentLinkTransformer;

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
     * @param array $attributes
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(array $attributes);
}