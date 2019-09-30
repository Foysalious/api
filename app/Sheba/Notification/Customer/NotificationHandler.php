<?php namespace App\Sheba\Notification\Customer;

use App\Models\Customer;

class NotificationHandler
{
    protected $customer;
    protected $agent;
    public $notifications = [];

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function notification(...$avatar)
    {
        $all_notifications = [];
        foreach ($avatar as $agent) {
            $class_name = 'App\Sheba\Notification\Customer\\' . ucfirst(camel_case($agent));
            $class = app($class_name);
            $class->setCustomer($this->customer);
            $all_notifications = array_merge($all_notifications, $class->getNotification());
        }
        return $all_notifications;
    }
}