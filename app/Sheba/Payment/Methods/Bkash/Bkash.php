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
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\Bkash\Response\ExecuteResponse;
use Sheba\Payment\Methods\PaymentMethod;
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
    /** @var BkashAuth */
    private $bkashAuth;
    /** @var Registrar $registrar */
    private $registrar;

    public function __construct(Registrar $registrar)
    {
        parent::__construct();
        $this->registrar = $registrar;
    }

    /**
     * @param BkashAuth $bkashAuth
     * @return Bkash
     */
    private function setBkashAuth(BkashAuth $bkashAuth): Bkash
    {
        $this->bkashAuth = $bkashAuth;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $invoice = "SHEBA_BKASH_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, 1, 1);
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice) {
            $payment->payable_id = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->gateway_transaction_id = $invoice;
            $payment->status = 'initiated';
            $payment->valid_till = $this->getValidTill();
            $this->setModifier($payable->user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method = self::NAME;
            $payment_details->amount = $payable->amount;
            $payment_details->save();
        });
        $bkash_agreement_id = $payment->payable->getUserProfile()->getAgreementId(PaymentStrategy::BKASH);
        $credential_dto = (new BkashCredentialDto())->setUser($payable->user)->setUserType($payable->type)->setTokenizedId($bkash_agreement_id);
        $this->setCredentials($credential_dto);
        if ($bkash_agreement_id) {
            /** @var TokenizedPayment $tokenized_payment */
            $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
            $data = $tokenized_payment->setBkashAuth($this->bkashAuth)->create($payment);
            $payment->redirect_url = $data->bkashURL;
        } else {
            $data = $this->create($payment);
            $payment->redirect_url = config('sheba.front_url') . '/bkash?paymentID=' . $data->paymentID;
        }
        $payment->gateway_transaction_id = $data->paymentID;
        $payment->transaction_details = json_encode($data);
        $payment->update();
        return $payment;
    }

    /**
     * @param $user
     * @param $type
     * @throws Exception
     */
    private function setCredentials(BkashCredentialDto $credential_dto)
    {
        $bkash_auth = BkashAuthBuilder::getForUserAndType($credential_dto);
        $this->setBkashAuth($bkash_auth);
        $this->appKey = $bkash_auth->appKey;
        $this->appSecret = $bkash_auth->appSecret;
        $this->username = $bkash_auth->username;
        $this->password = $bkash_auth->password;
        $this->url = $bkash_auth->url;
        $this->merchantNumber = $bkash_auth->merchantNumber;
    }

    private function create(Payment $payment)
    {
        $token = Redis::get('BKASH_TOKEN');
        $token = $token ? $token : $this->grantToken();
        $intent = 'sale';
        $create_pay_body = json_encode(array(
            'amount' => $payment->payable->amount,
            'currency' => 'BDT',
            'intent' => $intent,
            'merchantInvoiceNumber' => $payment->gateway_transaction_id
        ));
        $url = curl_init($this->url . '/checkout/payment/create');
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
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret
        );
        $url = curl_init($this->url . '/checkout/token/grant');
        $post_token = json_encode($post_token);
        $header = array(
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
        $data = json_decode($result_data, true);
        $token = $data['id_token'];
        Redis::set('BKASH_TOKEN', $token);
        Redis::expire('BKASH_TOKEN', (int)$data['expires_in'] - 100);
        return $token;
    }

    /**
     * @param Payment $payment
     * @return Payment|mixed
     * @throws GuzzleException
     * @throws Exception
     */
    public function validate(Payment $payment): Payment
    {
        $bkash_agreement_id = $payment->payable->getUserProfile()->getAgreementId(PaymentStrategy::BKASH);
        $credential_dto = (new BkashCredentialDto())->setUser($payment->payable->user)->setUserType($payment->payable->type)->setTokenizedId($bkash_agreement_id);
        $this->setCredentials($credential_dto);
        $execute_response = new ExecuteResponse();
        $execute_response->setPayment($payment);
        if ($bkash_agreement_id) {
            /** @var TokenizedPayment $tokenized_payment */
            $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment')->setBkashAuth($this->bkashAuth);
            $res = $tokenized_payment->setBkashAuth($this->bkashAuth)->execute($payment);
        } else {
            $res = $this->execute($payment);
        }
        $execute_response->setResponse($res);
        $this->paymentLogRepo->setPayment($payment);
        if ($execute_response->hasSuccess()) {
            $success = $execute_response->getSuccess();
            try {
                $this->registrar->setAmount($payment->payable->amount)
                    ->setDetails(json_encode($success->details))
                    ->setTime(Carbon::now()->format('Y-m-d H:i:s'))
                    ->setIsValidated(1)
                    ->register($payment->payable->user, 'bkash', $success->id, $this->merchantNumber);
                $status = Statuses::VALIDATED;
                $transaction_details = json_encode($success->details);
            } catch (InvalidTransaction $e) {
                $status = Statuses::VALIDATION_FAILED;
                $transaction_details = json_encode(['errorMessage' => $e->getMessage(), 'gateway_response' => $success->getGatewayResponse()]);
            }
        } else {
            $error = $execute_response->getError();
            $status = Statuses::VALIDATION_FAILED;
            $transaction_details = json_encode($error->details);
        }
        $this->paymentLogRepo->create([
            'to' => $status,
            'from' => $payment->status,
            'transaction_details' => $transaction_details
        ]);
        $payment->status = $status;
        $payment->transaction_details = $transaction_details;
        $payment->update();
        return $payment;
    }

    private function execute(Payment $payment)
    {
        $token = Redis::get('BKASH_TOKEN');
        $token = $token ? $token : $this->grantToken();
        $url = curl_init($this->url . '/checkout/payment/execute/' . $payment->gateway_transaction_id);
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
            $error = new InvalidArgumentException('Bkash execute API error.');
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
