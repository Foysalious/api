<?php namespace Sheba\SmsCampaign\DTO;


use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrder;
use Sheba\PresentableDTO;

class SmsCampaignOrderDTO extends PresentableDTO
{
    /** @var SmsCampaignOrder */
    private $order;

    public function __construct(SmsCampaignOrder $order = null)
    {
        if ($order) $this->setOrder($order);
    }

    public function setOrder(SmsCampaignOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getId()
    {
        return $this->order->id;
    }

    public function getTotalCost()
    {
        return $this->order->total_cost;
    }

    public function getTitle()
    {
        return $this->order->title;
    }

    public function getFormattedCreatedAt($format = 'Y-m-d H:i:s')
    {
        return $this->order->created_at->format($format);
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'total_cost' => $this->getTotalCost(),
            'title' => $this->getTitle(),
            'message' => $this->order->message,
            'total_messages_requested' => $this->order->total_messages,
            'successfully_sent' => $this->order->successful_messages,
            'messages_pending' => $this->order->pending_messages,
            'messages_failed' => $this->order->failed_messages,
            'sms_count' => $this->order->orderReceivers[0]->sms_count,
            'sms_rate' => $this->order->rate_per_sms,
            'created_at' => $this->getFormattedCreatedAt()
        ];
    }
}