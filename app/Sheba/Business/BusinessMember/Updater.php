<?php namespace Sheba\Business\BusinessMember;

use phpseclib\Crypt\AES;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessMember;

class Updater
{
    use ModificationFields;

    /** BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var Requester $requester */
    private $requester;
    /** BusinessMember $businessMember */
    private $businessMember;
    private $businessMemberData = [];

    /**
     * Updater constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->businessMemberRepository = $business_member_repository;
    }

    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function update(array $data)
    {
        /*$business_member_data = [
            'business_role_id' => $this->requester->getRole() ? $this->requester->getRole() : $this->businessMember->business_role_id,
            'manager_id' => $this->requester->getManagerEmployee() ? $this->requester->getManagerEmployee() : $this->businessMember->manager_id,
            'join_date' => $this->requester->getJoinDate() ? $this->requester->getJoinDate() : $this->businessMember->join_date,
            'grade' => $this->requester->getGrade() ? $this->requester->getGrade() : $this->businessMember->grade,
            'employee_type' => $this->requester->getEmployeeType() ? $this->requester->getEmployeeType() : $this->businessMember->employee_type,
            'previous_institution' => $this->requester->getPreviousInstitution() ? $this->requester->getPreviousInstitution() : $this->businessMember->previous_institution,
            'status' => $this->requester->getStatus() ? $this->requester->getStatus() : $this->businessMember->status
        ];*/
        $this->formatData($data);
        return $this->businessMemberRepository->update($this->businessMember, $this->withUpdateModificationField($this->businessMemberData));
    }

    private function formatData($data)
    {
        /** Basic */
        if (isset($data['business_role_id'])) $this->businessMemberData['business_role_id'] = $data['business_role_id'];
        if (isset($data['manager_id'])) $this->businessMemberData['manager_id'] = $data['manager_id'];
        /** Official */
        if (isset($data['join_date'])) $this->businessMemberData['join_date'] = $data['join_date'];
        if (isset($data['grade'])) $this->businessMemberData['grade'] = $data['grade'];
        if (isset($data['employee_type'])) $this->businessMemberData['employee_type'] = $data['employee_type'];
        if (isset($data['previous_institution'])) $this->businessMemberData['previous_institution'] = $data['previous_institution'];
        /** Status */
        if (isset($data['status'])) $this->businessMemberData['status'] = $data['status'];
    }
}
