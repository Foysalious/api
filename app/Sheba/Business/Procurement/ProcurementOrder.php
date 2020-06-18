<?php namespace App\Sheba\Business\Procurement;

use App\Models\Bid;
use App\Models\Procurement;
use League\Fractal\Manager;
use Illuminate\Http\Request;
use League\Fractal\Resource\Item;
use App\Transformers\CustomSerializer;
use League\Fractal\Resource\Collection;
use App\Transformers\Business\ProcurementOrderListTransformer;
use App\Transformers\Business\ProcurementOrderDetailsTransformer;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class ProcurementOrder
{
    /** @var Procurement $procurement */
    private $procurement;
    /** @var Bid $bid */
    private $bid;

    /** @var ProcurementRepositoryInterface $procurementRepository */
    private $procurementRepository;

    /**
     * ProcurementOrder constructor.
     * @param ProcurementRepositoryInterface $procurement_repository
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository)
    {
        $this->procurementRepository = $procurement_repository;
    }

    /**
     * @param $procurement
     * @return $this
     */
    public function setProcurement($procurement)
    {
        $this->procurement = $this->procurementRepository->find((int)$procurement);
        return $this;
    }

    /**
     * @param Bid $bid
     * @return $this
     */
    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    /**
     * @param $procurement_orders
     * @param Request $request
     * @return array
     */
    public function orders($procurement_orders, Request $request)
    {
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurement_orders->get(), new ProcurementOrderListTransformer());
        $procurement_orders = $manager->createData($resource)->toArray()['data'];

        if ($request->has('status') && $request->status != 'all') $procurement_orders = $this->filterWithStatus($procurement_orders, $request->status);


        if ($request->has('sort_by_id')) $procurement_orders = $this->sortById($procurement_orders, $request->sort_by_id)->values();
        if ($request->has('sort_by_title')) $procurement_orders = $this->sortByTitle($procurement_orders, $request->sort_by_title)->values();
        if ($request->has('sort_by_vendor')) $procurement_orders = $this->sortByVendor($procurement_orders, $request->sort_by_vendor)->values();
        if ($request->has('sort_by_status')) $procurement_orders = $this->sortByStatus($procurement_orders, $request->sort_by_status)->values();
        if ($request->has('sort_by_created_at')) $procurement_orders = $this->sortByCreatedAt($procurement_orders, $request->sort_by_created_at)->values();

        $total_orders = count($procurement_orders);
        list($offset, $limit) = calculatePagination($request);
        if ($request->has('limit')) $procurement_orders = collect($procurement_orders)->splice($offset, $limit);

        return [$procurement_orders, $total_orders];
    }

    /**
     * @return mixed
     */
    public function orderDetails()
    {
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($this->procurement, new ProcurementOrderDetailsTransformer($this->bid));
        $procurement = $fractal->createData($resource)->toArray()['data'];
        return $procurement;
    }

    /**
     * @param $procurements
     * @param $status
     * @return \Illuminate\Support\Collection
     */
    private function filterWithStatus($procurements, $status)
    {
        if ($status === 'accepted') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'process') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'served') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'cancelled') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortById($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['id']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByTitle($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['title']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByVendor($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['bid']['service_provider']['name']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByStatus($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['status']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByCreatedAt($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['created_at']);
        });
    }
}