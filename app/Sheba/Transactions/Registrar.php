<?php namespace Sheba\Transactions;

use App\Models\Partner;

class Registrar
{
    /**
     * @param $user
     * @param $gateway
     * @param $transaction_id
     * @return bool
     * @throws InvalidTransaction
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function register($user, $gateway, $transaction_id)
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
            'created_by_name' => $user instanceof Partner ? $user->name : $user->profile->name
        ];

        $walletClient = new WalletClient();
        $response_wallet = json_decode(json_encode($walletClient->registerTransaction($data)), 1);

        if($response_wallet['code'] != 200) {
            throw new InvalidTransaction($response_wallet['message']);
        }

        return $response_wallet['transaction'];
    }
}