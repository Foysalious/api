<?php

namespace App\Sheba\AccountingEntry\Repository;


use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class DueTrackerReminderRepository extends AccountingRepository
{

    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param $data
     * @return void
     */
    public function createReminder($partner,$data)
    {
        $url = "api/reminders/";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->post($url, $data);
    }

    /**
     * @param $partner
     * @return array
     */
    public function getReminders($partner,$query_string){
        $url = "api/reminders/?".$query_string;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($partner->id)->get($url);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateReminder($data){
        //TODO: will update the reminder through post api
        return $data;
    }

    /**
     * @param $reminder_id
     * @return mixed
     */
    public function  deleteReminder($reminder_id){
        //TODO: will delete the reminder through post api
        return $reminder_id;
    }
}