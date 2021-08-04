<?php


namespace Tests\Unit\Accounting;


use App\Sheba\AccountingEntry\Repository\PaymentLinkRepository;
use Tests\Unit\UnitTestCase;

class PaymentLinkAccountingEntryTest extends UnitTestCase
{
    private $payload = [
        "amount" => 50,
        "bank_transaction_charge" => 2,
        "interest" => 0,
        "source_type" => 'payment_link',
        "debit_account_key" => 'payment_link_service_charge',
        "credit_account_key" => 'income_from_payment_link'
    ];
    private $partnerId = 38015;

    public function test_payment_link()
    {
        /** @var PaymentLinkRepository $paymentLinkRepo */
        $paymentLinkRepo = app(PaymentLinkRepository::class);

        $response = $paymentLinkRepo->setAmount($this->payload['amount'])
            ->setBankTransactionCharge($this->payload['bank_transaction_charge'])
            ->setInterest($this->payload['interest'])
            ->store($this->partnerId);
        $this->assertTrue($response['amount'] == $this->payload['amount']);
    }
}