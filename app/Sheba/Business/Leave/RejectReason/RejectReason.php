<?php namespace Sheba\Business\Leave\RejectReason;

class RejectReason
{
    public function reasons()
    {
        $reject_reasons = Reason::getReasons();
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
