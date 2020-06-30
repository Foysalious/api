<?php namespace App\Transformers\Business;

use App\Models\Procurement;
use League\Fractal\TransformerAbstract;
use Sheba\Business\Procurement\StatusCalculator as ProcurementStatusCalculator;

class ProcurementInvitationListTransformer extends TransformerAbstract
{
    /** @var Procurement $procurement */
    private $procurement;

    /**
     * ProcurementInvitationListTransformer constructor.
     * @param Procurement $procurement
     */
    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function transform($invitation)
    {
        $partner = $invitation->partner;
        return [
            'vendor' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'logo' => $partner->logo,
            ],
            'status' => $this->generateStatus($partner),
            'procurement_status' => ProcurementStatusCalculator::resolveStatus($this->procurement),
            'invited_on' => $invitation->created_at->format('h:i a').','.$invitation->created_at->format('d/m/y')
        ];
    }

    private function generateStatus($partner)
    {
        $bidder_ids = $this->procurement->bids->pluck('bidder_id')->toArray();
        if (in_array($partner->id, $bidder_ids)) return 'Participated';
        return 'Not Participated';
    }
}
