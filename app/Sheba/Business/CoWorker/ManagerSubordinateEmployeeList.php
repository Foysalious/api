<?php namespace App\Sheba\Business\CoWorker;


use App\Transformers\Business\CoWorkerManagerListTransformer;
use App\Transformers\CustomSerializer;
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
    public function get($business_member)
    {
        $first_level_managers = $this->getCoWorkersUnderSpecificManager($business_member->id);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $managers_data = [];
        if ($first_level_managers->count() > 0)
            foreach ($first_level_managers as $first_level_manager) {
                $resource = new Item($first_level_manager, new CoWorkerManagerListTransformer());
                $managers_data[] = $manager->createData($resource)->toArray()['data'];
                $second_level_managers = $this->getCoWorkersUnderSpecificManager($first_level_manager->id);
                if ($second_level_managers->count() > 0)
                    foreach ($second_level_managers as $second_level_manager) {
                        $resource = new Item($second_level_manager, new CoWorkerManagerListTransformer());
                        $managers_data[] = $manager->createData($resource)->toArray()['data'];
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

    private function getCoWorkersUnderSpecificManager($business_member_id)
    {
        return $this->businessMemberRepository->where('manager_id', $business_member_id)->get();
    }
}