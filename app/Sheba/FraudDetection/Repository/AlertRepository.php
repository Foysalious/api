<?php namespace Sheba\FraudDetection\Repository;

use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;

class AlertRepository extends BaseRepository
{

    /**
     * @return mixed
     * @throws FraudDetectionServerError
     */
    public function getAllAlerts()
    {
        $result = $this->client->get('/alerts');
        return $result['alert_list'];
    }
    public function getAlertDetail($id)
    {
        $result = $this->client->get('/alert/'.$id);
        return $result['alert'];
    }

    public function update($data, $entryId)
    {
        $result = $this->client->post('/alert/' . $entryId, $data);
        return $result['alert'];
    }


}
