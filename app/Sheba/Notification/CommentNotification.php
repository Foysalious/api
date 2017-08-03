<?php namespace Sheba\Notification;

use App\Models\CustomOrder;
use App\Models\Job;
use App\Models\InfoCall;
use App\Models\Flag;
use App\Models\Complain;
use Illuminate\Support\Facades\Auth;

class CommentNotification
{
    private $authUser;

    public function __construct()
    {
        $auth_user = Auth::user();
        $this->authUser = $auth_user->department->name . " - " . $auth_user->name;
    }

    public function send($commentable)
    {
        $commentable_type = explode('\\', get_class($commentable))[2];
        $this->$commentable_type($commentable);
    }

    private function Job(Job $job)
    {
        if(!empty($job->crm)) {
            notify()->user($job->crm)->send([
                'title' => $this->authUser . " commented on job " . $job->id,
                'link'  => url("job/$job->id") . "#comments-section",
                'type'  => notificationType('Info')
            ]);
        }
    }

    private function Flag(Flag $flag)
    {
        notify($flag->department, $flag->byDepartment)->send([
            'title' => $this->authUser . " commented on flag " . $flag->id,
            'link'  => url("flag/$flag->id") . "#comments-section",
            'type'  => notificationType('Info')
        ]);
    }

    private function InfoCall(InfoCall $infoCall)
    {
       // notify($infoCall->crm())->send("");
    }

    private function Complain(Complain $complain)
    {
        // dd($complain->job);
        // $this->Job($complain->job);
        notify()->department(13)->send([
            'title' => $this->authUser . " commented on complain " . $complain->id,
            'link'  => url("complain/$complain->id") . "#comments-section",
            'type'  => notificationType('Danger')
        ]);
    }

    public function CustomOrder(CustomOrder $customOrder)
    {
        // notify($customOrder->crm)->send("");
    }
}