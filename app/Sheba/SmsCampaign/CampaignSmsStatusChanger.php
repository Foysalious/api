<?php namespace Sheba\SmsCampaign;

use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiverRepository;
use Sheba\SmsCampaign\Jobs\CampaignSmsStatusChangeJob;

class CampaignSmsStatusChanger
{
    /** @var SmsCampaignOrderReceiverRepository */
    private $receiverRepo;

    public function __construct(SmsCampaignOrderReceiverRepository $receiver_repo)
    {
        $this->receiverRepo = $receiver_repo;
    }

    public function processPendingSms()
    {
        foreach ($this->receiverRepo->getAllPending() as $log) {
            dispatch(new CampaignSmsStatusChangeJob($log));
        }
    }
}