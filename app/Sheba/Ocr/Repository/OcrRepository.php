<?php namespace Sheba\Ocr\Repository;

class OcrRepository extends BaseRepository
{
    /**
     * @param $data
     * @return mixed
     */
    public function nidCheck($data)
    {
        $result = $this->client->post('/parse-nid', $data);
        return $result['data'];
    }
}
