<?php namespace Sheba\Order\Code;

use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;

class Builder extends Machine
{
    /**
     * @param Order $order
     * @return mixed
     */
    public function channel(Order $order)
    {
        return $this->getChannelCode($order->sales_channel);
    }

    /**
     * @param Order $order
     * @return string
     */
    public function order(Order $order)
    {
        return $this->channel($order) . self::SEPARATOR . $this->getOrderCodeById($order->id);
    }

    /**
     * @param PartnerOrder $partner_order
     * @return string
     */
    public function partnerOrder(PartnerOrder $partner_order)
    {
        return $this->order($partner_order->order) . self::SEPARATOR . $this->getPartnerCodeById($partner_order->partner_id);
    }

    /**
     * @param Job $job
     * @return string
     */
    public function job(Job $job)
    {
        return $this->getJobCodeById($job->id);
    }

    /**
     * @param Job $job
     * @return string
     */
    public function jobFull(Job $job)
    {
        return $this->partnerOrder($job->partnerOrder) . self::SEPARATOR . $this->job($job);
    }

    /**
     * @param $sales_channel
     * @param $order_id
     * @param null $partner_id
     * @param null $job_id
     * @return string
     */
    public function byScratch($sales_channel, $order_id, $partner_id = null, $job_id = null)
    {
        if(!$this->isValidChannel($sales_channel)) throw new \InvalidArgumentException('Not a good channel');

        $code = $this->getChannelCode($sales_channel) . self::SEPARATOR . $this->getOrderCodeById($order_id);
        if($partner_id) $code .= self::SEPARATOR . $this->getPartnerCodeById($partner_id);
        if($job_id) $code .= self::SEPARATOR . $this->getJobCodeById($job_id);
        return $code;
    }

    /**
     * @return int
     */
    public function getOrderStartCode()
    {
        return self::$ORDER_CODE_START;
    }

    private function getOrderFormat()
    {
        return '%' . self::PAD_STRING . self::ORDER_CODE_LENGTH . 'd';
    }

    private function getJobFormat()
    {
        return '%' . self::PAD_STRING . self::JOB_CODE_LENGTH . 'd';
    }

    private function getChannelCode($channel)
    {
        return self::$SALES_CHANNEL_PREFIXES[$channel];
    }

    private function getOrderCodeById($id)
    {
        return sprintf($this->getOrderFormat(), $id + self::$ORDER_CODE_START);
    }

    private function getPartnerCodeById($id)
    {
        return str_pad($id, self::PARTNER_CODE_LENGTH, self::PAD_STRING, STR_PAD_LEFT);
    }

    private function getJobCodeById($id)
    {
        return sprintf($this->getJobFormat(), $id + self::$JOB_CODE_START);
    }

}