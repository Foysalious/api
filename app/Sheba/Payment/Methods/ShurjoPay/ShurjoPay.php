<?php namespace Sheba\Payment\Methods\ShurjoPay;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\ShurjoPay\Response\InitResponse;
use Sheba\Payment\Methods\ShurjoPay\Response\ValidationResponse;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\Payment\Statuses;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPRequest;
use Sheba\Transactions\Wallet\HasWalletTransaction;

class ShurjoPay extends PaymentMethod
{
    /** @var DynamicSslStoreConfiguration */
    private $configuration;
    /**
     * @var TPProxyClient
     */
    private $tpClient;

    public function setConfiguration(DynamicSslStoreConfiguration $configuration): ShurjoPay
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function __construct(TPProxyClient $tp_client)
    {
        parent::__construct();
        $this->tpClient = $tp_client;
        $this->baseUrl = config('payment.shurjopay.base_url');
    }

    public function init(Payable $payable): Payment
    {
        if (!$payable->isPaymentLink()) throw  new \Exception('Only Payment Link payment will work');
        $this->setConfiguration($this->getCredentials($payable));
        $payment = $this->createPayment($payable, PaymentStrategy::SHURJOPAY);
        $response = $this->createSecretPayment($payment);
        $init_response = new InitResponse();
        $init_response->setResponse($response);
        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($response);
            $payment->redirect_url = $success->redirect_url;
            $payment->gateway_transaction_id = $success->id;
        } else {
            $error = $init_response->getError();
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to' => Statuses::INITIATION_FAILED,
                'from' => $payment->status,
                'transaction_details' => json_encode($error->details)
            ]);
            $payment->status = Statuses::INITIATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    private function getReceiver(Payable $payable): HasWalletTransaction
    {
        $payment_link = $payable->getPaymentLink();
        return $payment_link->getPaymentReceiver();
    }

    private function getCredentials(Payable $payable): DynamicSslStoreConfiguration
    {
        $shurjo_pay = new ShurjoPayStore();
        $shurjo_pay->setPartner($this->getReceiver($payable));
        $gateway_account = $shurjo_pay->getStoreAccount(PaymentStrategy::SHURJOPAY);
        return (new DynamicSslStoreConfiguration($gateway_account->configuration));
    }

    private function createSecretPayment(Payment $payment)
    {
        $token = $this->getToken();
        $request = (new TPRequest())->setUrl($this->baseUrl . '/secret-pay')
            ->setMethod(TPRequest::METHOD_POST)->setInput([
                'token' => $token->token,
                'store_id' => $token->store_id,
                'prefix' => 'sp',
                'currency' => 'BDT',
                'return_url' => config('sheba.api_url') . '/v2/shurjopay/validate',
                'cancel_url' => config('sheba.api_url') . '/v2/shurjopay/validate',
                'amount' => $payment->payable->amount,
                'order_id' => $payment->id,
                'customer_name' => $payment->payable->getName(),
                'customer_phone' => $payment->payable->getMobile(),
                'customer_address' => 'Dhaka',
                'customer_city' => 'Dhaka',
                'client_ip' => getIp()
            ]);
        return $this->tpClient->call($request);
    }

    private function getToken()
    {
        $request = (new TPRequest())->setUrl($this->baseUrl . '/get_token')
            ->setMethod(TPRequest::METHOD_POST)->setInput([
                'username' => $this->configuration->getStoreId(),
                'password' => $this->configuration->getPassword()
            ]);
        return $this->tpClient->call($request);
    }

    public function validate(Payment $payment): Payment
    {
        $this->setConfiguration($this->getCredentials($payment->payable));
        $token = $this->getToken();
        $request = (new TPRequest())->setUrl($this->baseUrl . '/payment-status')
            ->setMethod(TPRequest::METHOD_POST)->setInput([
                'order_id' => $payment->gateway_transaction_id,
                'token' => $token->token
            ]);
        $response = $this->tpClient->call($request);
        $validation_response = new ValidationResponse();
        $validation_response->setResponse($response[0])->setPayment($payment);
        $this->paymentLogRepo->setPayment($payment);
        if ($validation_response->hasSuccess()) {
            $success = $validation_response->getSuccess();
            $this->paymentLogRepo->create([
                'to' => Statuses::VALIDATED,
                'from' => $payment->status,
                'transaction_details' => $payment->transaction_details
            ]);
            $payment->status = Statuses::VALIDATED;
            $payment->transaction_details = json_encode($success->details);
        } else {
            $error = $validation_response->getError();
            $this->paymentLogRepo->create([
                'to' => Statuses::VALIDATION_FAILED,
                'from' => $payment->status,
                'transaction_details' => $payment->transaction_details
            ]);
            $payment->status = Statuses::VALIDATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }

    public function getCalculatedChargedAmount($transaction_details)
    {
        return 0;
    }
}