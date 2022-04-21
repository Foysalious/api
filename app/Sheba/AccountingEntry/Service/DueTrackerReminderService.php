<?php

namespace App\Sheba\AccountingEntry\Service;

use App\Models\Partner;
use App\Sheba\AccountingEntry\Notifications\ReminderNotificationHandler;
use App\Sheba\AccountingEntry\Repository\DueTrackerReminderRepository;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

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
    protected $start_date;
    protected $end_date;
    protected $offset;
    protected $limit;
    protected $order_by;

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
     * @param mixed $order_by
     * @return $this
     */
    public function setOrderBy($order_by): DueTrackerReminderService
    {
        $this->order_by = $order_by;
        return $this;
    }

    /**
     * @param mixed $limit
     * @return $this
     */
    public function setLimit($limit): DueTrackerReminderService
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return $this
     */
    public function setOffset($offset): DueTrackerReminderService
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $start_date
     * @return $this
     */
    public function setStartDate($start_date): DueTrackerReminderService
    {
        $this->start_date = $start_date;
        return $this;
    }

    /**
     * @param $end_date
     * @return $this
     */
    public function setEndDate($end_date): DueTrackerReminderService
    {
        $this->end_date = $end_date;
        return $this;
    }

    /**
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function createReminder()
    {
        $data = $this->makeDataForReminderCreate();
        return $this->dueTrackerReminderRepo->createReminder($this->partner, $data);
    }

    /**
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getReminders(): array
    {
        $query_string = $this->generateQueryString();
        return $this->dueTrackerReminderRepo->getReminders($this->partner, $query_string);
    }

    /**
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function update()
    {
        $data = $this->makeDataForReminderUpdate();
        return $this->dueTrackerReminderRepo->updateReminder($this->partner, $data);
    }

    /**
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function delete()
    {
        return $this->dueTrackerReminderRepo->deleteReminder($this->partner, $this->reminder_id);
    }

    /**
     * @return array
     */
    private function makeDataForReminderCreate(): array
    {
        $data['contact_type'] = $this->contact_type;
        $data['contact_id'] = $this->contact_id;
        $data['should_send_sms'] = $this->sms;
        $data['reminder_at'] = $this->reminder_date;
        return $data;
    }

    /**
     * @return array
     */
    private function makeDataForReminderUpdate(): array
    {
        $data['reminder_id'] = $this->reminder_id;
        if ($this->sms == 0) {
            $data['should_send_sms'] = false;
        } else {
            $data['should_send_sms'] = true;
        }
        $data['reminder_at'] = $this->reminder_date;
        $data['reminder_status'] = $this->reminder_status;
        $data['sms_status'] = $this->sms_status;
        return $data;
    }

    /**
     * @return string
     */
    private function generateQueryString(): string
    {
        $query_strings = [];
        if (isset($this->order_by)) {
            $query_strings [] = 'order_by=' . $this->order_by;
            $query_strings [] = isset($this->order) ? 'order=' . strtolower($this->order) : 'order=desc';
        }
        if (isset($this->start_date) && isset($this->end_date)) {
            $query_strings [] = "start_date=$this->start_date";
            $query_strings [] = "end_date=$this->end_date";
        }
        if (isset($this->limit) && isset($this->offset)) {
            $query_strings [] = "limit=$this->limit";
            $query_strings [] = "offset=$this->offset";
        }
        if (isset($this->contact_type)) {
            $query_strings [] = "contact_type=" . strtolower($this->contact_type);
        }
        if (isset($this->reminder_status)) {
            $query_strings [] = "reminder_status=" . $this->reminder_status;
        }

        return implode('&', $query_strings);
    }

    /**
     * @param array $reminder
     * @return bool
     * @throws AccountingEntryServerError
     */
    public function sendReminderPush(array $reminder): bool
    {
        $push = (new ReminderNotificationHandler())->setReminder($reminder)->handler();
        Log::info(['remidner push', $push, $reminder]);
        $smsStatus = false;
        if ($reminder['should_send_sms'] == 1) {
//          TODO: send SMS
//            $sms_content["balance"] = '';
//            $sms_content["balance_type"] = '';
//            $sms_content["contact_name"] = '';
//            $sms_content["contact_mobile"] = '';
//            $response = $this->dueTrackerSmsService->setPartnerId($partner_id)
//                ->setContactType($contact_type)
//                ->sendSmsForReminder($sms_content);
            $smsStatus = true;
        }
        $this->setReminderId($reminder['id'])
            ->setSms($reminder['should_send_sms'])
            ->setReminderDate($reminder['reminder_date'])
            ->setReminderStatus('success')
            ->setSmsStatus($smsStatus)
            ->update();
        return true;
    }

}