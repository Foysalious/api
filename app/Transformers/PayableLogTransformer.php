<?php namespace App\Transformers;

use App\Models\Profile;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PayableLogTransformer extends TransformerAbstract
{
    /**
     * @param array $payable_log
     * @return array
     */
    public function transform(array $payable_log)
    {
        $entry_at = Carbon::parse($payable_log['entry_at']);
        return [
            'id' => $payable_log['id'],
            'amount' => $payable_log['amount'],
            'updated_by' => $payable_log['created_by_name'],
            'updated_date' => $entry_at->format('Y-m-d'),
            'updated_time' => $entry_at->format('h:s A')
        ];
    }
}
