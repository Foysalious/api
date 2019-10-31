<?php namespace Sheba\FraudDetection\Repository;


use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;

class RulesRepository extends BaseRepository
{

    /**
     * @return mixed
     * @throws FraudDetectionServerError
     */
    public function getAllRules()
    {
        $result = $this->client->get('/alert_rules');
        return $result['alert_rules'];
    }

    /**
     * @param $data
     * @param $entryId
     * @return mixed
     * @throws FraudDetectionServerError
     */
    public function update($data, $entryId)
    {
        $result = $this->client->post('/alert_rules/' . $entryId, $data);
        return $result['alert_rule'];
    }

    /**
     * @param $data
     * @return array
     * @throws FraudDetectionServerError
     */
    public function store($data)
    {
        return $this->client->post('/alert_rules', $data);
    }
}
