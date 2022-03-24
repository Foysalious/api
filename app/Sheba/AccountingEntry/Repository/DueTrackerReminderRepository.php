<?php

namespace App\Sheba\AccountingEntry\Repository;


use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerReminderRepository extends AccountingRepository
{

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param $partner
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function createReminder($partner,$data)
    {
        $url = "api/reminders/";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
    }

    /**
     * @param $partner
     * @param $query_string
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getReminders($partner,$query_string): array
    {
        $url = "api/reminders/?".$query_string;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->get($url);
    }

    /**
     * @param $partner
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function updateReminder($partner,$data){
        $url = "api/reminders/".$data['reminder_id'];
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->put($url, $data);

    }

    /**
     * @param $partner
     * @param $reminder_id
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function  deleteReminder($partner,$reminder_id){
        $url = "api/reminders/".$reminder_id;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->delete($url);
    }
}