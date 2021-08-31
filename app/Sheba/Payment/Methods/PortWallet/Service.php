<?php namespace Sheba\Payment\Methods\PortWallet;

use App\Models\Payment;
use Sheba\Payment\Methods\PortWallet\Response\InitResponse;
use Sheba\Payment\Methods\PortWallet\Response\RefundResponse;
use Sheba\Payment\Methods\PortWallet\Response\ValidateResponse;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;

class Service
{
    /** @var Client */
    private $client;

    private $isIpnEnabled;
    /** @var Payment */
    private $payment;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->isIpnEnabled = config('payment.port_wallet.is_ipn_enabled');
    }

    /**
     * @param Payment $payment
     * @return Service
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return InitResponse|PaymentMethodResponse
     */
    public function createInvoice()
    {
        $data = $this->makeCreateData();

        if ($this->isIpnEnabled) {
            $data['order']['redirect_url'] = config('payment.port_wallet.urls.redirect_without_validation');
            $data['order']['ipn_url'] = config('payment.port_wallet.urls.ipn');
        } else {
            $data['order']['redirect_url'] = config('payment.port_wallet.urls.validation_on_redirect');
        }

        $res = $this->client->post('/invoice', $data);
        return (new InitResponse())->setPayment($this->payment)->setResponse($res);
    }

    /**
     * @return ValidateResponse|PaymentMethodResponse
     */
    public function validate()
    {
        return $this->isIpnEnabled ? $this->validateIpn() : $this->retrieveInvoice();
    }

    /**
     * @return ValidateResponse|PaymentMethodResponse
     */
    public function validateIpn()
    {
        $res = $this->client->get("/invoice/ipn/" . $this->payment->gateway_transaction_id . "/" . $this->payment->payable->amount);
        return $this->createValidationResponse($res);
    }

    /**
     * @return ValidateResponse|PaymentMethodResponse
     */
    public function retrieveInvoice()
    {
        $res = $this->client->get("/invoice/" . $this->payment->gateway_transaction_id);
        return $this->createValidationResponse($res);
    }

    /**
     * @param $amount
     * @return RefundResponse|PaymentMethodResponse
     */
    public function refund($amount)
    {
        $res = $this->client->post('/invoice/refund/' . $this->payment->gateway_transaction_id, [
            'refund' => [
                'amount' => $amount,
                'currency' => "BDT"
            ]
        ]);

        return (new RefundResponse())->setPayment($this->payment)->setResponse($res);
    }

    private function makeCreateData()
    {
        $payable = $this->payment->payable;
        $profile = $payable->getUserProfile();

        $data = [
            'order' => [
                'amount' => $payable->amount,
                'currency' => 'BDT',
                'validity' => $this->payment->getValidityInSeconds(),
            ],
            'product' => [
                'name' => normalizeStringCases($payable->readable_type),
                'description' => $payable->description ?? 'Sheba Platform Limited',
            ],
            'billing' => [
                // they are not happy with invalid/null data
                'customer' => [
                    'name' => $profile->name,
                    'email' => $profile->email ?: config('sheba.email'),
                    'phone' => $profile->mobile,
                    'address' => [
                        'street' => $profile->address ?: config('sheba.address'),
                        'city' => 'Dhaka',
                        'state' => 'dhk',
                        'zipcode' => '1213',
                        'country' => 'BD'
                    ]
                ]
            ]
        ];

        if ($payable->amount >= config('sheba.min_order_amount_for_emi') && $payable->emi_month) {
            $data['emi']['enable'] = 1;
            $data['emi']['tenures'] = [(int)$payable->emi_month];
        }

        return $data;
    }

    private function createValidationResponse($res)
    {
        return (new ValidateResponse())->setPayment($this->payment)->setResponse($res);
    }
}
