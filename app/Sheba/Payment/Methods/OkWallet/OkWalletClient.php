<?php namespace Sheba\Payment\Methods\OkWallet;

use App\Models\Payment;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\OkWallet\Response\InitResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class OkWalletClient
{
    /** @var TPProxyClient */
    private $tpClient;
    /** @var string $baseUrl */
    private $baseUrl;
    /** @var string $apiUser */
    private $apiUser;
    /** @var string $apiPassword */
    private $apiPassword;
    /** @var string $merchantId */
    private $merchantId;
    /** @var string $accessKey */
    private $accessKey;
    /** @var TPRequest $tpRequest */
    private $tpRequest;
    /** @var array $urls */
    private $urls;

    /**
     * TPProxyClient constructor.
     *
     * @param TPProxyClient $tp_client
     * @param TPRequest $request
     */
    public function __construct(TPProxyClient $tp_client, TPRequest $request)
    {
        $this->tpClient = $tp_client;
        $this->tpRequest = $request;
        $this->baseUrl = config('payment.ok_wallet.base_url');
        $this->apiUser = config('payment.ok_wallet.api_user');
        $this->apiPassword = config('payment.ok_wallet.api_password');
        $this->merchantId = config('payment.ok_wallet.merchant_id');
        $this->accessKey = config('payment.ok_wallet.access_key');
        $this->urls = config('payment.ok_wallet.urls');
    }

    /**
     * @param $order_id
     * @return string
     */
    public static function getTransactionUrl($order_id): string
    {
        return config('payment.ok_wallet.web_client_base_url') . '/' . $order_id;
    }

    /**
     * @param $transaction_id
     * @return array
     * @throws TPProxyServerError
     */
    public function validationRequest($transaction_id): array
    {
        $request = (new TPRequest())
            ->setMethod(TPRequest::METHOD_POST)
            ->setUrl("$this->baseUrl/getTransaction/$this->apKey/$transaction_id");
        $response = $this->tpClient->call($request);
        return (array)$response;
    }

    /**
     * @return mixed
     * @throws TPProxyServerError
     */
    private function getToken()
    {
        $token_url = $this->baseUrl . '?request=generate_token&rand=' . time();
        $get_token = [
            'api_user' => $this->apiUser,
            'api_pass' => $this->apiPassword,
            'merchantID' => $this->merchantId
        ];
        $tp_request = $this->tpRequest;
        $tp_request->setUrl($token_url)
            ->setMethod(TPRequest::METHOD_POST)
            ->setInput($get_token);

        $response = $this->tpClient->call($tp_request);

        return $response->token;
    }

    /**
     * @param float $amount
     * @param string $trx_id
     * @return InitResponse
     * @throws FailedToInitiateException
     */
    public function createOrder(float $amount, string $trx_id): InitResponse
    {
        try {
            $order_create_url = $this->baseUrl . '?request=create_order&rand=' . time();
            $token = $this->getToken();

            $input_data = [
                'api_user' => $this->apiUser,
                'api_pass' => $this->apiPassword,
                'merchantID' => $this->merchantId,
                'accessKey' => $this->accessKey,
                'token' => $token,
                'amount' => $amount,
                'approved_url' => $this->urls['approve'] . "?order_id=$trx_id",
                'decline_url' => $this->urls['decline'] . "?order_id=$trx_id",
                'cancel_url' => $this->urls['cancel']. "?order_id=$trx_id",
                'pay_description' => 'SHEBA_OKWALLET_PAYMENT_TRX:' . $trx_id,
                'billNo' => $trx_id
            ];

            $request = $this->tpRequest
                ->setMethod(TPRequest::METHOD_POST)
                ->setUrl($order_create_url)
                ->setInput($input_data);

            $response = $this->tpClient->call($request);

            return new InitResponse((array)$response);
        } catch (TPProxyServerError $e) {
            throw new FailedToInitiateException($e->getMessage());
        }
    }

    /**
     * @param Payment $payment
     * @return InitResponse
     * @throws FailedToInitiateException
     */
    public function validateOrder(Payment $payment): InitResponse
    {
        $payable = $payment->payable;
        try {
            $order_verify_url = $this->baseUrl . '?request=verify_order&rand=' . time();
            $input_data = [
                'api_user' => $this->apiUser,
                'api_pass' => $this->apiPassword,
                'merchantID' => $this->merchantId,
                'accessKey' => $this->accessKey,
                'amount' => $payable->amount,
                'OrderID' => $payment->gateway_transaction_id,
                'bill_no' => $payment->transaction_id
            ];

            $request = $this->tpRequest
                ->setMethod(TPRequest::METHOD_POST)
                ->setUrl($order_verify_url)
                ->setInput($input_data);

            $response = $this->tpClient->call($request);

            return new InitResponse((array)$response);
        } catch (TPProxyServerError $e) {
            throw new FailedToInitiateException($e->getMessage());
        }
    }
}
