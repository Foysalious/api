<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Carbon\Carbon;

class PartnerTransactionRepository
{
    private $partner;

    function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    /**
     * @param $data
     * @param null $tags
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Exception
     */
    public function save($data, $tags = null)
    {
        $transaction = null;
        if ($data['amount'] > 0) {
            $data['created_at'] = Carbon::now();
            $transaction = $this->partner->transactions()->save(new PartnerTransaction($data));
            (new PartnerRepository())->updateWallet($this->partner, $data['amount'], $data['type']);
            if (is_array($tags) && !empty($tags[0])) $transaction->tags()->sync($tags);
        }
        return $transaction;
    }

    public function hasSameDetails($details)
    {
        $details = json_decode($details, 1);
        $gateway = $details['gateway'];
        $transaction_id = $details['transaction']['id'];
        return PartnerTransaction::where('transaction_details', 'LIKE', '%"gateway":"' . $gateway . '"%')
                ->where('transaction_details', 'LIKE', '%"transaction":{"id":"' . $transaction_id . '"%')
                ->count() > 0;
    }
}