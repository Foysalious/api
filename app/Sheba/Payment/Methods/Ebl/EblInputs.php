<?php


namespace Sheba\Payment\Methods\Ebl;


use App\Models\Payment;
use Carbon\Carbon;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\Payment\Methods\Ebl\Stores\EblStore;

class EblInputs
{
    use ProtectedGetterTrait;

    protected $access_key;
    protected $amount;
    protected $currency;
    protected $locale;
    protected $profile_id;
    protected $reference_number;
    protected $signed_date_time;
    protected $signed_field_names;
    protected $transaction_type;
    protected $transaction_uuid;
    protected $signature;
    protected $unsigned_field_names;
    private   $secret_key;
    /** @var Payment $payment */
    private $payment;

    public function __construct(EblStore $store)
    {
        $this->access_key         = $store->getAccessKey();
        $this->locale             = $store->getLocal();
        $this->profile_id         = $store->getProfileId();
        $this->currency           = 'BDT';
        $this->transaction_type   = 'authorization,create_payment_token';
        $this->signed_field_names = $store->getSignedFieldNames();
        $this->signed_date_time   = Carbon::now()->format('Y-m-d\TH:i:s\Z');
        $this->secret_key         = $store->getSecretKey();


    }

    /**
     * @param Payment $payment
     * @return EblInputs
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    public function generate()
    {
        return $this->generateData()->sign();

    }

    private function generateData()
    {
        $this->amount           = $this->payment->payable->amount;
        $this->transaction_uuid = $this->payment->gateway_transaction_id;
        $this->reference_number = $this->payment->id;
        return $this;

    }

    private function sign()
    {
        $this->signature = base64_encode(hash_hmac('sha256', $this->buildDataToSign(), $this->secret_key, true));
        return $this;
    }

    private function buildDataToSign()
    {
        $dataToSign       = [];
        $signedFieldNames = explode(',', $this->signed_field_names);
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . $this->$field;
        }
        return implode(",", $dataToSign);
    }

}
