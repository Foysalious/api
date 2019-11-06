<?php namespace App\Sheba\Bondhu\Repository;


use Sheba\Bondhu\Repository\BaseRepository;

class OcrRepository extends BaseRepository
{
    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function nidCheck($data)
    {
        $result = $this->client->post('/parse-nid', $data);
        return $result['data'];
    }

}
