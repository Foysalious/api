<?php

if(!function_exists('notify')) {
    /**
     * NotificationHandler wrapper.
     *
     * @param $notifiable = to be notified
     * @return \Sheba\Notification\NotificationHandler
     */
    function notify(...$notifiable)
    {
        $notificationHandler = new \Sheba\Notification\NotificationHandler();
        if($notifiable) {
            if(count($notifiable) == 1) {
                $notifiable = array_pop($notifiable);
            }
            $notificationHandler->setNotifiable($notifiable);
        }
        return $notificationHandler;
    }
}

if(!function_exists('getNotificationTypes')) {
    /**
     * View helper function for notification types.
     *
     * @return array
     */
    function getNotificationTypes()
    {
        extract(constants('NOTIFICATION_TYPES'));
        return [
            $Info => ['icon' => 'bullhorn', 'class' => 'info'],
            $Warning => ['icon' => 'bell-o', 'class' => 'warning'],
            $Danger => ['icon' => 'bolt', 'class' => 'danger'],
            $Success => ['icon' => 'check-square', 'class' => 'success'],
        ];
    }
}

if(!function_exists('notificationType')) {
    /**
     * Get notification type for specific key or full type array.
     *
     * @param $key
     * @return mixed
     */
    function notificationType($key = null)
    {
        return ( ($key) ? constants('NOTIFICATION_TYPES')[$key] : constants('NOTIFICATION_TYPES') );
    }
}