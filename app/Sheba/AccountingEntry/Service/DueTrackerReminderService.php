<?php

namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Repository\DueTrackerReminderRepository;

class DueTrackerReminderService
{
    protected $partner;
    protected $contact_type;
    protected $contact_id;
    protected $sms;
    protected $reminder_date;
    protected $reminder_status;
    protected $sms_status;
    protected $reminder_id;
    /**
     * @var DueTrackerReminderRepository
     */
    private $dueTrackerReminderRepo;

    public function __construct(DueTrackerReminderRepository $dueTrackerReminderRepo)
    {
        $this->dueTrackerReminderRepo = $dueTrackerReminderRepo;
    }

    /**
     * @param $reminder_id
     * @return $this
     */
    public function setReminderId($reminder_id): DueTrackerReminderService
    {
        $this->reminder_id = $reminder_id;
        return $this;
    }
    /**
     * @param mixed $contact_type
     * @return DueTrackerReminderService
     */
    public function setContactType($contact_type): DueTrackerReminderService
    {
        $this->contact_type = $contact_type;
        return $this;
    }
    /**
     * @param $contact_id
     * @return $this
     */
    public function setContactId($contact_id): DueTrackerReminderService
    {
        $this->contact_id = $contact_id;
        return $this;
    }
    /**
     * @param mixed $partner
     * @return DueTrackerReminderService
     */
    public function setPartner($partner): DueTrackerReminderService
    {
        $this->partner = $partner;
        return $this;
    }
    /**
     * @param $sms
     * @return $this
     */
    public function setSms($sms): DueTrackerReminderService
    {
        $this->sms = $sms;
        return $this;
    }

    /**
     * @param $reminder_date
     * @return $this
     */
    public function setReminderDate($reminder_date): DueTrackerReminderService
    {
        $this->reminder_date = $reminder_date;
        return $this;
    }

    /**
     * @param $reminder_status
     * @return $this
     */
    public function setReminderStatus($reminder_status): DueTrackerReminderService
    {
        $this->reminder_status = $reminder_status;
        return $this;
    }

    /**
     * @param $sms_status
     * @return $this
     */
    public function setSmsStatus($sms_status): DueTrackerReminderService
    {
        $this->sms_status = $sms_status;
        return $this;
    }
    /**
     * @return array
     */
    private function makeDataForReminderCreate(): array
    {
        $data['partner_id']= $this->partner->id;
        $data['contact_type']= $this->contact_type;
        $data['contact_id']= $this->contact_id;
        $data['sms']= $this->sms;
        $data['reminder_date']= $this->reminder_date;
        $data['reminder_status']= $this->reminder_status;
        $data['sms_status']= $this->sms_status;
        return $data;
    }
    private function makeDataForReminderUpdate(){
        $data['reminder_id']= $this->reminder_id;
        $data['sms']= $this->sms;
        $data['reminder_date']= $this->reminder_date;
        $data['reminder_status']= $this->reminder_status;
        $data['sms_status']= $this->sms_status;
        return $data;
    }

    /**
     * @return void
     */
    public function createReminder(){
        $data = $this->makeDataForReminderCreate();
        return $this->dueTrackerReminderRepo->createReminder($data);
    }

    /**
     * @return array
     */
    public function getReminders(): array
    {
        return $this->dueTrackerReminderRepo->getReminders($this->partner);
    }

    /**
     * @return mixed
     */
    public function update(){
        $data = $this->makeDataForReminderUpdate();
        return $this->dueTrackerReminderRepo->updateReminder($data);
    }
}