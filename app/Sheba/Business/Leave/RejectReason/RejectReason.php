<?php namespace Sheba\Business\Leave\RejectReason;

class RejectReason
{
    public function reasons()
    {
        $reject_reasons = Reason::getReasonsV2();
        $reasons = [];
        foreach ($reject_reasons as $key => $reject_reason) {
            $reasons[] = [
                'key' => $key,
                'value' => $reject_reason
            ];
        }
        return $reasons;
    }
}
