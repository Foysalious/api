<?php namespace Sheba\Business\ProcurementInvitation;


use App\Models\Partner;
use App\Models\Procurement;
use Sheba\Dal\ProcurementInvitation\ProcurementInvitationRepositoryInterface;

class Creator
{
    private $procurementInvitationRepository;
    private $partner;
    private $procurement;

    public function __construct(ProcurementInvitationRepositoryInterface $procurement_invitation_repository)
    {
        $this->procurementInvitationRepository = $procurement_invitation_repository;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function create()
    {
        $procurement_invitation = $this->procurementInvitationRepository->create([
            'partner_id' => $this->partner->id,
            'procurement_id' => $this->procurement->id,
        ]);
        return $procurement_invitation;
    }
}