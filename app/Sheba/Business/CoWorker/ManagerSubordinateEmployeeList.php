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
     * @return array
     */
    public function get($business_member)
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

                        /** @var  $third_level_managers */
                        $third_level_managers = $this->getCoWorkersUnderSpecificManager($second_level_manager->id);
                        if ($third_level_managers->count() > 0)
                            foreach ($third_level_managers as $third_level_manager) {
                                $resource = new Item($third_level_manager, new CoWorkerManagerListTransformer());
                                $managers_data[] = $manager->createData($resource)->toArray()['data'];
                            }
                    }
            }
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
}