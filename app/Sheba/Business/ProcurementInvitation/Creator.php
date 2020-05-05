<?php namespace Sheba\Business\ProcurementInvitation;

use App\Models\BusinessMember;
use App\Models\Partner;
use App\Models\Procurement;
use Sheba\Dal\ProcurementInvitation\ProcurementInvitationRepositoryInterface;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;

class Creator
{
    use HasErrorCodeAndMessage, ModificationFields;

    private $procurementInvitationRepository;
    private $partner;
    private $procurement;
    private $procurementInvitation;

    public function __construct(ProcurementInvitationRepositoryInterface $procurement_invitation_repository)
    {
        $this->procurementInvitationRepository = $procurement_invitation_repository;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        if ($this->procurement) $this->checkDuplicateInvitation();
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->setModifier($business_member);
        return $this;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        if ($this->partner) $this->checkDuplicateInvitation();
        return $this;
    }

    public function create()
    {
        $data = $this->withCreateModificationField([
            'partner_id' => $this->partner->id,
            'procurement_id' => $this->procurement->id,
        ]);
        return $this->procurementInvitationRepository->create($data);
    }

    private function checkDuplicateInvitation()
    {
        $procurement_invitation = $this->procurementInvitationRepository->findByProcurementPartner($this->procurement, $this->partner);
        if ($procurement_invitation) {
            $this->setError(409, 'Duplicate Entry');
            $this->procurementInvitation = $procurement_invitation;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProcurementInvitation()
    {
        return $this->procurementInvitation;
    }
}
