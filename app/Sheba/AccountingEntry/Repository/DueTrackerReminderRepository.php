<?php

namespace App\Sheba\AccountingEntry\Repository;

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
    public function createReminder($data)
    {
        //TODO: will create the reminder through post api
        return $data;
    }

    public function getReminders($partner){
        //TODO: will get the reminders for that partner
        //dd($partner->id);
        $data['list'] = [[
                "id" => 1,
                "partner_id" => 217122,
                "contact_type" => "customer",
                "contact_id" => "first contact id here",
                "sms" => "1",
                "reminder_date" => "2022-03-23",
                "reminder_status" => "upcoming",
                "sms_status" => "Will Send SMS"
            ],
            [
                "id" => 2,
                "partner_id" => 217122,
                "contact_type" => "customer",
                "contact_id" => "second contact id here",
                "sms" => "1",
                "reminder_date" => "2022-03-25",
                "reminder_status" => "upcoming",
                "sms_status" => "Will Send SMS"
            ]];
        return $data;
    }
}