<?php namespace Sheba\Comment;

use App\Models\Flag;
use Exception;

class FlagNotificationHandler extends NotificationHandler
{
    /**
     * @throws Exception
     */
    public function handle()
    {
        /** @var Flag $flag */
        $flag = $this->commentable;
        notify($this->flagNotifiables($flag))->send([
            'title' => $this->authUserName . " commented on flag " . $flag->id,
            'link' => url("flag/$flag->id") . "#comments-section",
            'type' => notificationType('Info')
        ]);
    }

    /**
     * @param Flag $flag
     * @return array
     */
    private function flagNotifiables(Flag $flag)
    {
        if (!$flag->assigned_by_id && !$flag->assigned_to_id) {
            return [$flag->department, $flag->byDepartment];
        }

        if ($flag->assigned_by_id && !$flag->assigned_to_id) {
            if ($flag->assigned_by_id == $this->authUserId) return [$flag->department];

            return [$flag->department, $flag->assignedBy];
        }

        if (!$flag->assigned_by_id && $flag->assigned_to_id) {
            if ($flag->assigned_to_id == $this->authUserId) return [$flag->byDepartment];

            return [$flag->byDepartment, $flag->assignedTo];
        }

        if ($flag->assigned_to_id == $this->authUserId) return [$flag->assignedBy];
        if ($flag->assigned_by_id == $this->authUserId) return [$flag->assignedTo];
        return [$flag->assignedBy, $flag->assignedTo];
    }
}
