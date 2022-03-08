<?php namespace App\Sheba\Business\CoWorker;

use App\Transformers\Business\CoWorkerManagerListTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Repositories\Business\BusinessMemberRepository;

class ManagerSubordinateEmployeeList
{
    /*** @var BusinessMemberRepository $businessMemberRepository */
    private $businessMemberRepository;

    public function __construct()
    {
        $this->businessMemberRepository = app(BusinessMemberRepository::class);
    }

    /**
     * @param $business_member
     * @param null $department
     * @param null $is_employee_active
     * @return array
     */
    public function get($business_member, $department = null, $is_employee_active = null)
    {
        $managers_data = [];
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());

        /** @var  $first_level_managers */
        $first_level_managers = $this->getCoWorkersUnderSpecificManager($business_member->id);
        if ($first_level_managers->count() > 0)
            foreach ($first_level_managers as $first_level_manager) {
                $resource = new Item($first_level_manager, new CoWorkerManagerListTransformer());
                $managers_data[] = $manager->createData($resource)->toArray()['data'];

                /** @var  $second_level_managers */
                $second_level_managers = $this->getCoWorkersUnderSpecificManager($first_level_manager->id);
                if ($second_level_managers->count() > 0)
                    foreach ($second_level_managers as $second_level_manager) {
                        $resource = new Item($second_level_manager, new CoWorkerManagerListTransformer());
                        $managers_data[] = $manager->createData($resource)->toArray()['data'];
                    }
            }
        $managers_data = $this->uniqueManagerData($managers_data);
        if ($department) return $this->filterEmployeeByDepartment($business_member, $managers_data, $is_employee_active);
        return $managers_data;
    }

    /**
     * @param $business_member_id
     * @return Collection
     */
    private function getCoWorkersUnderSpecificManager($business_member_id)
    {
        return $this->businessMemberRepository->where('manager_id', $business_member_id)->get();
    }

    /**
     * @param $business_member
     * @param $managers_data
     * @param $is_employee_active
     * @return array
     */
    private function filterEmployeeByDepartment($business_member, $managers_data, $is_employee_active)
    {
        $filtered_unique_managers_data = $this->removeSpecificBusinessMemberIdFormUniqueManagersData($business_member, $managers_data);

        $data = [];
        foreach ($filtered_unique_managers_data as $manager) {
            if ($is_employee_active && $manager['is_active'] == 0) continue;
            $data[$manager['department']][] = $manager;
        }
        return $data;
    }

    /**
     * @param $managers_data
     * @return array
     */
    private function uniqueManagerData($managers_data)
    {
        $out = [];
        foreach ($managers_data as $row) {
            $out[$row['id']] = $row;
        }
        return array_values($out);
    }

    /**
     * @param $business_member
     * @param $unique_managers_data
     * @return array
     */
    public function removeSpecificBusinessMemberIdFormUniqueManagersData($business_member, $unique_managers_data)
    {
        return array_filter($unique_managers_data, function ($manager_data) use ($business_member) {
            return ($manager_data['id'] != $business_member->id);
        });
    }
}