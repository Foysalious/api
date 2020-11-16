<?php namespace Sheba\Comment;

use Exception;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Notification\ComplainNotification;
use Sheba\Notification\ComplainNotificationPartner;

class ComplainNotificationHandler extends NotificationHandler
{
    /**
     * @throws Exception
     */
    public function handle()
    {
        /** @var Complain $complain */
        $complain = $this->commentable;

        if (config('sheba.portal') == 'admin-portal') {
            $complainNotification = (new ComplainNotification($complain));
            $complainNotification->notifyQcOnComment();
            if ($this->accessors) $complainNotification->notifyAccessorOnComment($this->accessors);

            return;
        }

        (new ComplainNotificationPartner($complain))->notifyQcAndCrmOnComment();
    }
}
