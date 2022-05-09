<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\BusinessShift\BusinessShift;

class ShiftDetailsTransformer extends TransformerAbstract
{
    public function transform(BusinessShift $shift)
    {
        return [
            'id' =>  $shift->id,
            'name' =>   $shift->name,
            'title' => $shift->title,
            'start_time' => Carbon::parse($shift->start_time)->format('h:i A'),
            'start_grace_time' => $shift->checkin_grace_time,
            'end_time' => Carbon::parse($shift->end_time)->format('h:i A'),
            'end_grace_time' => $shift->checkout_grace_time,
            'is_half_day_active' => $shift->is_halfday_enable,
            'created_at' => $shift->created_at->format('h:i A d/m/Y'),
            'updated_at' => $shift->updated_at->format('h:i A d/m/Y'),
            'created_by' => str_replace('Member-', '', $shift->created_by_name),
            'updated_by' => $shift->updated_by_name ? str_replace('Member-', '', $shift->updated_by_name) : null
        ];
    }
}
