<?php

namespace Database\Factories;

use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;

class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition()
    {
        return array_merge($this->commonSeeds, [
            'requestable_type' => 'Sheba\Dal\Leave\Model',
            'status'           => 'pending',
            'approver_id'      => '1',
            'is_notified'      => '1',
        ]);
    }
}
