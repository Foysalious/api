<?php namespace Sheba\Repositories\Interfaces;


interface PaymentLinkRepositoryInterface extends BaseRepositoryInterface
{
    public function statusUpdate($id, $status);

    public function paymentLinkDetails($id);
}