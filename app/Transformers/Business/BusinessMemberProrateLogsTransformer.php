<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class BusinessMemberProrateLogsTransformer extends TransformerAbstract
{
    public function transform($prorate_logs)
    {
        return [
            'id' => $prorate_logs->id,
            'log' => $prorate_logs->log,
            'created_date' => $prorate_logs->created_at->format('M j, Y'),
            'created_time' => $prorate_logs->created_at->format('h:i A'),
            'created_by' => $prorate_logs->created_by != 0 ? str_replace('Member-', '', $prorate_logs->created_by_name) : null
        ];
    }
}