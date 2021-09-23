<?php namespace App\Sheba\Business\OfficeSettingChangesLogs;

use League\Fractal\TransformerAbstract;

class ChangesLogsTransformer extends TransformerAbstract
{
    private $type = ['operational' => 'Operational', 'additional' => 'Additional'];
    public function transform($operational_changes_logs)
    {
        $created_at = $operational_changes_logs->created_at;
        return [
            'id' => $operational_changes_logs->id,
            'type' => $this->type[$operational_changes_logs->type],
            'logs' => $operational_changes_logs->logs,
            'created_at_date' => ($created_at)->format('j M, Y'),
            'created_at_time' => ($created_at)->format('h:i A'),
            'created_by_name' => str_replace('Member-', '', $operational_changes_logs->created_by_name)
        ];
    }

}