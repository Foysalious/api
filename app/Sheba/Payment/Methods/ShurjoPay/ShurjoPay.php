<?php namespace Sheba\Payment\Methods\ShurjoPay;

use App\Models\Payable;
use App\Models\Payment;
use GuzzleHttp\Client;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\DynamicStore;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\ShurjoPay\Response\InitResponse;
use Sheba\Payment\Methods\Ssl\Stores\DynamicSslStoreConfiguration;
use Sheba\Payment\Statuses;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\TPProxy\TPProxyClient;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Dal\PgwStoreAccount\Model as PgwStoreAccount;

class ShurjoPay extends PaymentMethod
{
    /** @var DynamicSslStoreConfiguration */
    private $configuration;

    public function setConfiguration(DynamicSslStoreConfiguration $configuration): ShurjoPay
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function __construct(TPProxyClient $tp_client)
    {
        parent::__construct();
        $this->successUrl = config('payment.ssl.urls.success');
        $this->failUrl = config('payment.ssl.urls.fail');
        $this->cancelUrl = config('payment.ssl.urls.cancel');
        $this->tpClient = $tp_client;
    }

    public function init(Payable $payable): Payment
    {
        $shurjo_pay = new ShurjoPayStore();
        $shurjo_pay->setPartner($this->getReceiver($payable));
//        $shurjo_pay->getStoreAccount(PaymentStrategy::SHURJOPAY);
        $payment = $this->createPayment($payable, PaymentStrategy::SHURJOPAY);
        $this->setConfiguration($this->getCredentials());
        $response = $this->createAPayment($payment);
        $init_response = new InitResponse();
        $init_response->setResponse($response);

        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($response);
            $payment->redirect_url = $success->redirect_url;
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

    private function getCredentials(): DynamicSslStoreConfiguration
    {
        $gateway_account = PgwStoreAccount::find(125);
        return (new DynamicSslStoreConfiguration($gateway_account->configuration));
    }

    private function createAPayment(Payment $payment)
    {
        $token = $this->getToken();
        $client = new Client();
        $response = $client->request('POST', 'https://sandbox.shurjopayment.com/api/secret-pay', [
            'form_params' => [
                'token' => $token['token'],
                'store_id' => $token['store_id'],
                'prefix' => 'sp',
                'currency' => 'BDT',
                'return_url' => 'https://api.dev-sheba.xyz/v2/shurjopay/validate',
                'cancel_url' => 'https://api.dev-sheba.xyz/v2/shurjopay/validate',
                'amount' => $payment->payable->amount,
                'order_id' => $payment->id,
                'customer_name' => 'Test',
                'customer_phone' => '01678242978',
                'customer_address' => 'Dhaka',
                'customer_city' => 'Dhaka',
                'client_ip' => getIp()
            ],
            'verify' => false
        ]);
        return json_decode($response->getBody());
    }

    private function getToken()
    {
        $client = new Client();
        $response = $client->request('POST', 'https://sandbox.shurjopayment.com/api/get_token', [
            'form_params' => [
                'username' => $this->configuration->getStoreId(),
                'password' => $this->configuration->getPassword()
            ],
            'verify' => false
        ]);
        return json_decode($response->getBody(), 1);
    }

//    private function get

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }
}