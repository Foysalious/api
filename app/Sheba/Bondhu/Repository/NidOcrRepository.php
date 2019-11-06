<?php namespace App\Sheba\Bondhu\Repository;


use Sheba\Bondhu\Repository\BaseRepository;

class NidOcrRepository extends BaseRepository
{
    public function nidCheck($uri,$data)
    {
        $result = $this->client->post('', $data);
        return $result['data'];
    }

}
