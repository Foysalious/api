<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrderPayment;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Sheba\Repositories\BaseRepository;

class PosOrderPaymentRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PosOrderPayment
     */
    public function save(array $data)
    {
        return PosOrderPayment::create($this->withCreateModificationField($data));
    }

    public function saveToNewPosOrderSystem($data)
    {
        $client = app(PosOrderServerClient::class);
        return $client->post('api/v1/payments', $data);
    }
}