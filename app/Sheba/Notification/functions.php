<?php

if (!function_exists('notify')) {
    /**
     * NotificationHandler wrapper.
     *
     * @param $notifiable = to be notified
     * @return \Sheba\Notification\NotificationHandler
     */
    function notify(...$notifiable)
    {
        $notificationHandler = new \Sheba\Notification\NotificationHandler();
        if ($notifiable) {
            if (count($notifiable) == 1) {
                $notifiable = array_pop($notifiable);
            }
            $notificationHandler->setNotifiable($notifiable);
        }
        return $notificationHandler;
    }
}

if (!function_exists('getNotificationTypes')) {
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

if (!function_exists('notificationType')) {
    /**
     * Get notification type for specific key or full type array.
     *
     * @param $key
     * @return mixed
     */
    function notificationType($key = null)
    {
        return (($key) ? constants('NOTIFICATION_TYPES')[$key] : constants('NOTIFICATION_TYPES'));
    }
}

if (!function_exists('getMonthsName')) {
    /**
     * Return months array.
     *
     * @param string $format
     * @return array
     */
    function getMonthsName($format = "m")
    {
        if ($format == "m") {
            return ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        } elseif ($format == "M") {
            return ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        }
    }
}


if(!function_exists('findStartEndDateOfAMonth')) {
    /**
     * @param $month
     * @param $year
     * @return array
     */
    function findStartEndDateOfAMonth($month = null, $year = null)
    {
        if ($month == 0 && $year != 0) {
            $start_time = \Carbon\Carbon::now()->year($year)->month(1)->day(1)->hour(0)->minute(0)->second(0);
            $end_time = \Carbon\Carbon::now()->year($year)->month(12)->day(31)->hour(23)->minute(59)->second(59);
            return ['start_time' => $start_time, 'end_time' => $end_time, 'days_in_month' => 31];
        } else {
            if (empty($month)) $month = \Carbon\Carbon::now()->month;
            if (empty($year)) $year = \Carbon\Carbon::now()->year;
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $start_time = \Carbon\Carbon::now()->year($year)->month($month)->day(1)->hour(0)->minute(0)->second(0);
            $end_time = \Carbon\Carbon::now()->year($year)->month($month)->day($days_in_month)->hour(23)->minute(59)->second(59);
            return ['start_time' => $start_time, 'end_time' => $end_time, 'days_in_month' => $days_in_month];
        }
    }
}

if (!function_exists('getSalesChannels')) {
    /**
     * Return Sales channel associative column (default to name).
     *
     * @param $key = The result column
     * @return Array
     */
    function getSalesChannels($key = 'name')
    {
        return array_combine(array_keys(constants('SALES_CHANNELS')), array_column(constants('SALES_CHANNELS'), $key));
    }
}