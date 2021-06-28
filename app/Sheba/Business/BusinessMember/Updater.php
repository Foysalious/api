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
        #$this->formatData($data);
        return $this->businessMemberRepository->update($this->businessMember, $this->withUpdateModificationField($data));
    }

    public function delete()
    {
        $this->businessMember->delete();
    }

    private function formatData($data)
    {
        if (isset($data['business_role_id'])) $this->businessMemberData['business_role_id'] = $data['business_role_id'];
        if (isset($data['manager_id'])) $this->businessMemberData['manager_id'] = $data['manager_id'];
        if (isset($data['join_date'])) $this->businessMemberData['join_date'] = $data['join_date'];
        if (isset($data['grade'])) $this->businessMemberData['grade'] = $data['grade'];
        if (isset($data['employee_type'])) $this->businessMemberData['employee_type'] = $data['employee_type'];
        if (isset($data['previous_institution'])) $this->businessMemberData['previous_institution'] = $data['previous_institution'];
        if (isset($data['status'])) $this->businessMemberData['status'] = $data['status'];
    }
}
