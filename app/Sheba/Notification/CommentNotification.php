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
        // notify($job->crm())->send("");
    }

    private function Flag(Flag $flag)
    {
        notify($flag->department, $flag->byDepartment)->send([
            'title' => $this->authUser . " commented on flag " . $flag->id,
            'link' => url("flag/$flag->id") . "#comments-section"
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
    }

    public function CustomOrder(CustomOrder $customOrder)
    {
        // notify($customOrder->crm)->send("");
    }
}