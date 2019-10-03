<?php namespace Sheba\Transactions;

use App\Models\Partner;
use GuzzleHttp\Exception\GuzzleException;

class Registrar
{
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
        $data = [
            'gateway' => $gateway,
            'transaction_id' => $transaction_id,
            'type' => 'credit',
            'used_on_type' => class_basename($user),
            'used_on_id' => $user->id,
            'portal' => $user instanceof Partner ? "manager-app" : "bondhu-app",
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'created_by' => $user->id,
            'created_by_type' => class_basename($user),
            'created_by_name' => $user instanceof Partner ? $user->name : $user->profile->name,
            'to_account' => $to_account
        ];

        $walletClient = new WalletClient();
        $response_wallet = json_decode(json_encode($walletClient->registerTransaction($data)), 1);

        if ($response_wallet['code'] != 200) {
            throw new InvalidTransaction($response_wallet['message']);
        }

        return $response_wallet['transaction'];
    }
}