<?php namespace Sheba\Order\Code;

class Breaker extends Machine
{
    private $channel = null;
    private $order = null;
    private $partner = null;
    private $job = null;

    private $codeComponents = [];
    private $codeComponentCount = 0;
    private $isInvalidCode = false;

    /**
     * @param  $code
     * @return mixed
     */
    public function get($code)
    {
        $this->codeComponents = explode(self::SEPARATOR, $code);
        $this->codeComponentCount = count($this->codeComponents);

        if(empty($code) || $this->isInvalidComponentNumber()) {
            $this->isInvalidCode = true;
        } else if ($this->codeComponentCount == 4) {
            $this->breakFourComponentCode();
        } else if ($this->codeComponentCount == 3) {
            $this->breakThreeComponentCode();
        } else if ($this->codeComponentCount == 2) {
            $this->breakTwoComponentCode();
        } else if ($this->codeComponentCount == 1) {
            $this->breakOneComponentCode();
        }

        return $this->format();
    }

    private function format()
    {
        return $this->isInvalidCode ? null : [
            'channel' => $this->channel,
            'order' => $this->order ? (intval($this->order) - self::$ORDER_CODE_START) : null,
            'partner' => $this->partner ? intval($this->partner) : null,
            'job' => $this->job ? (intval($this->job) - self::$JOB_CODE_START) : null,
        ];
    }

    private function isInvalidComponentNumber()
    {
        return $this->codeComponentCount < 1 || $this->codeComponentCount > 4;
    }

    private function breakFourComponentCode()
    {
        $this->channel = strtoupper($this->codeComponents[0]);
        if (!$this->isValidChannel($this->channel)) {
            $this->isInvalidCode = true;
            return;
        }
        $this->order = $this->codeComponents[1];
        $this->partner = $this->codeComponents[2];
        $this->job = $this->codeComponents[3];
    }

    private function breakThreeComponentCode()
    {
        if ($this->doesStartWithOrderCode()) {
            $this->breakThreeComponentCodeStartingWithOrder();
        } else {
            $this->breakThreeComponentCodeStartingWithChannel();
        }
    }

    private function breakTwoComponentCode()
    {
        if ($this->doesStartWithChannel()) {
            $this->breakTwoComponentCodeStartingWithChannel();
        } else if ($this->doesStartWithOrderCode()) {
            $this->breakTwoComponentCodeStartingWithOrder();
        } else if ($this->doesStartWithPartnerCode()) {
            $this->breakTwoComponentCodeStartingWithPartner();
        } else {
            $this->isInvalidCode = true;
        }
    }

    private function breakOneComponentCode()
    {
        if (!is_numeric($this->codeComponents[0])) {
            $this->isInvalidCode = true;
        } else if ($this->doesStartWithOrderCode()) {
            $this->order = $this->codeComponents[0];
        } else if ($this->doesStartWithJobCode()) {
            $this->job = $this->codeComponents[0];
        } else {
            $this->isInvalidCode = true;
        }
    }

    private function breakThreeComponentCodeStartingWithOrder()
    {
        $this->order = $this->codeComponents[0];
        $this->partner = $this->codeComponents[1];
        $this->job = $this->codeComponents[2];
    }

    private function breakThreeComponentCodeStartingWithChannel()
    {
        $this->channel = strtoupper($this->codeComponents[0]);
        if (!$this->isValidChannel($this->channel)) {
            $this->isInvalidCode = true;
            return;
        }
        $this->order = $this->codeComponents[1];
        $this->partner = $this->codeComponents[2];
    }

    private function breakTwoComponentCodeStartingWithChannel()
    {
        $this->channel = strtoupper($this->codeComponents[0]);
        if (!$this->isValidChannel($this->channel)) {
            $this->isInvalidCode = true;
        }
        $this->order = $this->codeComponents[1];
    }

    private function breakTwoComponentCodeStartingWithOrder()
    {
        $this->order = $this->codeComponents[0];
        $this->partner = $this->codeComponents[1];
    }

    private function breakTwoComponentCodeStartingWithPartner()
    {
        $this->partner = $this->codeComponents[0];
        $this->job = $this->codeComponents[1];
    }

    private function doesStartWithChannel()
    {
        return !is_numeric($this->codeComponents[0]) && strlen($this->codeComponents[0]) == self::CHANNEL_CODE_LENGTH;
    }

    private function doesStartWithOrderCode()
    {
        return is_numeric($this->codeComponents[0]) && strlen($this->codeComponents[0]) == self::ORDER_CODE_LENGTH;
    }

    private function doesStartWithPartnerCode()
    {
        return is_numeric($this->codeComponents[0]) && strlen($this->codeComponents[0]) == self::PARTNER_CODE_LENGTH;
    }

    private function doesStartWithJobCode()
    {
        return is_numeric($this->codeComponents[0]) && strlen($this->codeComponents[0]) == self::JOB_CODE_LENGTH;
    }
}