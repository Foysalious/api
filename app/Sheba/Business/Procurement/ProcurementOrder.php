<?php namespace App\Sheba\Business\Procurement;

use League\Fractal\Manager;
use Illuminate\Http\Request;
use App\Transformers\CustomSerializer;
use League\Fractal\Resource\Collection;
use Sheba\Business\Procurement\OrderStatusCalculator;
use App\Transformers\Business\ProcurementOrderTransformer;

class ProcurementOrder
{
    public function getOrders($procurement_orders, Request $request)
    {
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurement_orders->get(), new ProcurementOrderTransformer());
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
     * @param $procurements
     * @param $status
     * @return \Illuminate\Support\Collection
     */
    public function filterWithStatus($procurements, $status)
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