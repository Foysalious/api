<?php namespace Sheba\CmDashboard;

use App\Models\Job;
use Carbon\Carbon;
use Sheba\Repositories\ToDoRepository;

class OrderFollowupTask
{
    /** @var null */
    private $user;
    /** @var null */
    private $toDoRepo;

    public function __construct(ToDoRepository $toDoRepository)
    {
        $this->toDoRepo = $toDoRepository;
    }

    public function forUser($user)
    {
        $this->user = $user;
    }

    public function get()
    {
        return $this->makeData();
    }

    private function makeQuery()
    {
        return $this->toDoRepo->getRunningOrderTasks($this->user);
    }

    private function makeData()
    {
        $data = [];
        $target_data = [];

        $todo_tasks = $this->makeQuery();

        $jobs_id = $todo_tasks->pluck('focused_to_id')->toArray();
        Job::find($jobs_id)->each(function ($job) use (&$target_data) {
            $target_data[$job->id] = ['order_id' => $job->partnerOrder->order->id, 'order_code' => $job->partnerOrder->order->code()];
        });

        $todo_tasks->each(function ($todo_task) use (&$data, $target_data) {
            $data[] = [
                'task'          => $todo_task->task,
                'remaining_time'=> Carbon::now()->diffInHours($todo_task->due_date, false),
                'due_date'      => $todo_task->due_date,
                'order_id'      => $target_data[$todo_task->focused_to_id]['order_id'],
                'order_code'    => $target_data[$todo_task->focused_to_id]['order_code']
            ];
        });

        return collect($data)->sortBy('remaining_time')->values()->all();
    }
}