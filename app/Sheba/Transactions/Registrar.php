<?php namespace Sheba\Transactions;

use App\Models\Business;
use App\Models\Partner;
use GuzzleHttp\Exception\GuzzleException;

class Registrar
{
    private $amount, $from_account, $details, $time, $isValidated = 0;
    /** @var WalletClient */
    private $walletClient;

    /**
     * Registrar constructor.
     * @param WalletClient $wallet_client
     */
    public function __construct(WalletClient $wallet_client)
    {
        $this->walletClient = $wallet_client;
    }

    /**
     * @param mixed $isValidated
     * @return Registrar
     */
    public function setIsValidated($isValidated)
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    /**
     * @param mixed $time
     * @return Registrar
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return Registrar
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $from_account
     * @return Registrar
     */
    public function setFromAccount($from_account)
    {
        $this->from_account = $from_account;
        return $this;
    }

    /**
     * @param mixed $details
     * @return Registrar
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param $user
     * @param $gateway
     * @param $transaction_id
     * @param null $to_account
     * @return bool
     * @throws GuzzleException
     * @throws InvalidTransaction
     */
    public function register($user, $gateway, $transaction_id, $to_account = null)
    {
        $created_by = ($user instanceof Partner || $user instanceof Business) ? $user->name ?: 'Unknown Partner' : $user->profile->name ?: $user->profile->mobile;
        $data = [
            'gateway'         => $gateway,
            'transaction_id'  => $transaction_id,
            'type'            => 'credit',
            'used_on_type'    => class_basename($user),
            'used_on_id'      => $user->id,
            'portal'          => request()->hasHeader('Portal-Name') ? request()->header('Portal-Name') : (!is_null(request('portal_name')) ? request('portal_name') : config('sheba.portal')),
            'ip'              => request()->ip(),
            'user_agent'      => request()->header('User-Agent'),
            'created_by'      => $user->id,
            'created_by_type' => class_basename($user),
            'created_by_name' => $created_by,
            'to_account'      => $to_account,
            'amount'          => $this->amount,
            'details'         => $this->details,
            'from_account'    => $this->from_account,
            'time'            => $this->time,
            'is_validated'    => $this->isValidated
        ];

        $response_wallet = json_decode(json_encode($this->walletClient->registerTransaction($data)), 1);
        if ($response_wallet['code'] != 200) {
            throw new InvalidTransaction($response_wallet['message']);
        }
        return $response_wallet['transaction'];
    }
}
