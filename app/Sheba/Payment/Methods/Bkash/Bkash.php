<?php namespace Sheba\Payment\Methods\Bkash;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\BkashAuthBuilder;
use Sheba\Bkash\Modules\Tokenized\TokenizedPayment;
use Sheba\Bkash\ShebaBkash;
use Sheba\ModificationFields;
use Sheba\Payment\Exceptions\InvalidConfigurationException;
use Sheba\Payment\Methods\Bkash\Response\ExecuteResponse;
use Sheba\Payment\Methods\Bkash\Stores\BkashStore;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Response\PaymentMethodResponse;
use Sheba\Payment\Methods\Response\PaymentMethodSuccessResponse;
use Sheba\Payment\Statuses;
use Sheba\RequestIdentification;
use Sheba\Transactions\InvalidTransaction;
use Sheba\Transactions\Registrar;

class Bkash extends PaymentMethod
{
    use ModificationFields;

    const NAME = 'bkash';
    private $appKey;
    private $appSecret;
    private $username;
    private $password;
    private $url;
    private $merchantNumber;
    /** @var Registrar $registrar */
    private $registrar;
    /** @var BkashStore */
    private $store;

    public function __construct(Registrar $registrar)
    {
        parent::__construct();
        $this->registrar = $registrar;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $this->setStore($payable);

        $invoice = "SHEBA_BKASH_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, true, true);
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice) {
            $payment->payable_id             = $payable->id;
            $payment->transaction_id         = $invoice;
            $payment->gateway_account_name   = $this->store->getName();
            $payment->gateway_transaction_id = $invoice;
            $payment->status                 = 'initiated';
            $payment->valid_till             = $this->getValidTill();
            $this->setModifier($payable->user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details             = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method     = self::NAME;
            $payment_details->amount     = $payable->amount;
            $payment_details->save();
        });
        try {
            if (false && $payment->payable->user->getAgreementId()) {
                /** @var TokenizedPayment $tokenized_payment */
                $tokenized_payment               = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
                $data                            = $tokenized_payment->create($payment);
                $payment->gateway_transaction_id = $data->paymentID;
                $payment->redirect_url           = $data->bkashURL;
            } else {
                $data                            = $this->create($payment);
                $payment->gateway_transaction_id = $data->paymentID;
                $payment->redirect_url           = config('sheba.front_url') . '/bkash?paymentID=' . $data->paymentID;
            }
            $payment->transaction_details = json_encode($data);
            $payment->update();
        } catch (\Throwable $e) {
            $this->statusChanger->setPayment($payment)->changeToInitiationFailed($e->getMessage());
        }

        return $payment;
    }

    /**
     * @param $user
     * @param $type
     * @throws Exception
     */
    private function setCredentials($user, $type)
    {
        $bkash_auth = BkashAuthBuilder::getForUserAndType($user, $type);
        $this->setCredFromAuth($bkash_auth);
    }

    private function setCredFromAuth(BkashAuth $bkash_auth)
    {
        $this->appKey         = $bkash_auth->getAppKey();
        $this->appSecret      = $bkash_auth->getAppSecret();
        $this->username       = $bkash_auth->getUsername();
        $this->password       = $bkash_auth->getPassword();
        $this->url            = $bkash_auth->getUrl();
        $this->merchantNumber = $bkash_auth->getMerchantNumber();
    }

    /**
     * @throws Exception
     */
    private function setStore(Payable $payable)
    {
        $this->store = BkashAuthBuilder::getStore($payable);
        $bkash_auth  = $this->store->getAuth();
        $this->setCredFromAuth($bkash_auth);
    }

    private function create(Payment $payment)
    {
        $token           = Redis::get('BKASH_TOKEN');
        $token           = $token ? $token : $this->grantToken();
        $intent          = 'sale';
        $create_pay_body = json_encode(array(
            'amount'                => $payment->payable->amount,
            'currency'              => 'BDT',
            'intent'                => $intent,
            'merchantInvoiceNumber' => $payment->gateway_transaction_id
        ));
        return $this->initPayment($token, $create_pay_body);
    }

    private function initPayment($token, $create_pay_body)
    {
        $url    = curl_init($this->url . '/checkout/payment/create');
        $header = array(
            'Content-Type:application/json',
            'authorization:' . $token,
            'x-app-key:' . $this->appKey
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $create_pay_body);
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        if (curl_errno($url) > 0)
            throw new InvalidArgumentException('Bkash create API error.');
        curl_close($url);
        return json_decode($result_data);
    }

    private function grantToken()
    {
        $post_token = array(
            'app_key'    => $this->appKey,
            'app_secret' => $this->appSecret
        );
        $url        = curl_init($this->url . '/checkout/token/grant');
        $post_token = json_encode($post_token);
        $header     = array(
            'Content-Type:application/json',
            'password:' . $this->password,
            'username:' . $this->username
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        if (curl_errno($url) > 0)
            throw new InvalidArgumentException('Bkash grant token API error.');
        curl_close($url);
        $data  = json_decode($result_data, true);
        $token = $data['id_token'];
        Redis::set('BKASH_TOKEN', $token);
        Redis::expire('BKASH_TOKEN', (int)$data['expires_in'] - 100);
        return $token;
    }

    /**
     * @param Payment $payment
     * @return Payment|mixed
     * @throws Exception
     */
    public function validate(Payment $payment): Payment
    {
        $this->setStore($payment->payable);
        $execute_response = new ExecuteResponse();
        $execute_response->setPayment($payment);
        if (false && $payment->payable->user->getAgreementId()) {
            /** @var TokenizedPayment $tokenized_payment */
            $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
            $res               = $tokenized_payment->execute($payment);
        } else {
            $res = $this->execute($payment);
        }
        $execute_response->setResponse($res);
        $this->paymentLogRepo->setPayment($payment);
        if ($execute_response->hasSuccess()) {
            $success = $execute_response->getSuccess();
            try {
                $status              = Statuses::VALIDATED;
                $transaction_details = json_encode($success->details);
            } catch (InvalidTransaction $e) {
                $status              = Statuses::VALIDATION_FAILED;
                $transaction_details = json_encode(['errorMessage' => $e->getMessage(), 'gateway_response' => $success->getGatewayResponse()]);
            }
        } else {
            $error               = $execute_response->getError();
            $status              = Statuses::VALIDATION_FAILED;
            $transaction_details = json_encode($error->details);
        }
        $this->paymentLogRepo->create([
            'to'                  => $status,
            'from'                => $payment->status,
            'transaction_details' => $transaction_details
        ]);
        $payment->status              = $status;
        $payment->transaction_details = $transaction_details;
        $payment->update();
        return $payment;
    }

    private function execute(Payment $payment)
    {
        $token  = Redis::get('BKASH_TOKEN');
        $token  = $token ? $token : $this->grantToken();
        $url    = curl_init($this->url . '/checkout/payment/execute/' . $payment->gateway_transaction_id);
        $header = array(
            'authorization:' . $token,
            'x-app-key:' . $this->appKey
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        $result_data = json_decode($result_data);
        if (curl_errno($url) > 0) {
            $error            = new InvalidArgumentException('Bkash execute API error.');
            $error->paymentId = $payment->gateway_transaction_id;
            throw  $error;
        }
        curl_close($url);
        return $result_data;
    }

    public function getMethodName()
    {
        return self::NAME;
    }

    /**
     * @param BkashStore $store
     * @return bool
     * @throws InvalidConfigurationException
     */
    public function testInit(BkashStore $store)
    {
        $this->setCredFromAuth($store->getAuth());
        $token           = $this->grantToken();
        $intent          = 'sale';
        $create_pay_body = json_encode(array(
            'amount'                => 10,
            'currency'              => 'BDT',
            'intent'                => $intent,
            'merchantInvoiceNumber' => 'SHEBA_BKASH_TEST_INIT_' . randomString(5)
        ));
        $data            = $this->initPayment($token, $create_pay_body);
        if ($data->paymentID) {
            return true;
        }
        throw new InvalidConfigurationException("Invalid credentials! Please try again.");

    }

    public function getCalculatedChargedAmount($transaction_details)
    {
        if ($transaction_details->transactionStatus === "Completed") {
            $amount = (float)$transaction_details->amount;
            $charge = (float)config('bkash.transaction_charge');
            return round($amount * $charge / 100, 2);
        }
        return 0;
    }
}
