<?php namespace Sheba\Comment;

use App\Models\Job;
use Exception;

class JobNotificationHandler extends NotificationHandler
{
    /**
     * @throws Exception
     */
    public function handle()
    {
        /** @var Job $job */
        $job = $this->commentable;

        if (empty($job->crm)) return;

        $order = $job->partnerOrder->order;
        notify()->user($job->crm)->send([
            'title' => $this->authUserName . " commented on job " . $order->code(),
            'link' => url("order/$order->id") . "#comments-section",
            'type' => notificationType('Info')
        ]);
    }
}
