<?php namespace App\Sheba\UserMigration\Events;

use App\Events\Event;
use Sheba\Dal\UserMigration\Model as UserMigration;

class StatusUpdated extends Event
{
    /**
     * @var UserMigration
     */
    private $user_id;
    private $module_name;
    private $status;

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getModuleName()
    {
        return $this->module_name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function __construct($user_id, $module_name, $status)
    {
        $this->user_id = $user_id;
        $this->module_name = $module_name;
        $this->status = $status;
    }
}