<?php namespace Sheba\Repositories\Interfaces;


interface PaymentLinkRepositoryInterface extends BaseRepositoryInterface
{
    public function statusUpdate($id, array $data);
    public function paymentLinkDetails($id);
}