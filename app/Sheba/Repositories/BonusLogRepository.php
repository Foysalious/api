<?php namespace Sheba\Repositories;

use App\Models\BonusLog;

class BonusLogRepository extends BaseRepository
{
    /**
     * @param $data
     */
    public function storeCreditLog($data)
    {
        $data['type'] = 'Credit';
        $this->save($data);
    }

    /**
     * @param $data
     */
    public function storeDebitLog($data)
    {
        $data['type'] = 'Debit';
        $this->save($data);
    }

    /**
     * @param $data
     */
    private function save($data)
    {
        BonusLog::create($this->withCreateModificationField($data));
    }

    public function insert($data)
    {
        foreach ($data as $key => $item) {
            $data[$key] = $this->withCreateModificationField($item);
        }
        BonusLog::insert($data);
    }
}