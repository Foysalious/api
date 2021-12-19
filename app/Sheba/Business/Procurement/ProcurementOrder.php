<?php namespace App\Sheba\Business\Procurement;

use App\Models\Bid;
use App\Models\Procurement;
use Carbon\Carbon;
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
     * @return $this
     */
    public function getBid()
    {
        $this->bid = $this->procurement->getActiveBid();
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

        if ($request->filled('status') && $request->status != 'all') $procurement_orders = $this->filterWithStatus($procurement_orders, $request->status);
        if ($request->filled('search')) $procurement_orders = $this->searchByTitle($procurement_orders, $request)->values();
        if ($request->filled('sort_by_id')) $procurement_orders = $this->sortById($procurement_orders, $request->sort_by_id)->values();
        if ($request->filled('sort_by_title')) $procurement_orders = $this->sortByTitle($procurement_orders, $request->sort_by_title)->values();
        if ($request->filled('sort_by_vendor')) $procurement_orders = $this->sortByVendor($procurement_orders, $request->sort_by_vendor)->values();
        if ($request->filled('sort_by_status')) $procurement_orders = $this->sortByStatus($procurement_orders, $request->sort_by_status)->values();
        if ($request->filled('sort_by_created_at')) $procurement_orders = $this->sortByCreatedAt($procurement_orders, $request->sort_by_created_at)->values();

        $total_orders = count($procurement_orders);
        list($offset, $limit) = calculatePagination($request);
        if ($request->filled('limit')) $procurement_orders = collect($procurement_orders)->splice($offset, $limit);

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
        return $fractal->createData($resource)->toArray()['data'];
    }

    /**
     * @return array
     */
    public function formatTimeline()
    {
        $payment_requests = $this->procurement->paymentRequests()->with('statusChangeLogs')->get();
        $requests = [];
        $request_logs = [];
        foreach ($payment_requests as $payment_request) {
            $payment_request_logs = $payment_request->statusChangeLogs->isEmpty() ? null : $payment_request->statusChangeLogs;
            if ($payment_request_logs) {
                foreach ($payment_request_logs as $log) {
                    array_push($request_logs, [
                        'created_at' => $log->created_at->toDateTimeString(),
                        'time' => $log->created_at->format('h.i A'),
                        'date' => $log->created_at->format('Y-m-d'),
                        'log' => 'Status Updated From ' . $log->from_status . ' To ' . $log->to_status
                    ]);
                }
            }
            array_push($requests, [
                'created_at' => $payment_request->created_at->toDateTimeString(),
                'time' => $payment_request->created_at->format('h.i A'),
                'date' => $payment_request->created_at->format('Y-m-d'),
                'log' => 'This Payment Request: #' . $payment_request->id . ' Is ' . $payment_request->status
            ]);
        }
        $bid_status_change_log = $this->bid->statusChangeLogs()->where('to_status', 'awarded')->first();
        $data = [
            'created_at' => $bid_status_change_log ? $bid_status_change_log->created_at->toDateTimeString() : 'n/s',
            'time' => $bid_status_change_log ? $bid_status_change_log->created_at->format('h.i A') : 'n/s',
            'date' => $bid_status_change_log ? $bid_status_change_log->created_at->format('Y-m-d') : 'n/s',
            'log' => $bid_status_change_log ? 'Hired ' . $this->bid->bidder->name . ' and Status Updated From ' . $bid_status_change_log->from_status . ' To ' . $bid_status_change_log->to_status : 'n/s'
        ];

        $order_time_lines = collect(array_merge([$data], $requests, $request_logs))->sortByDesc('created_at')->groupBy('date');
        $order_time_line = [];
        foreach ($order_time_lines as $key => $time_lines) {
            array_push($order_time_line, [
                'date' => Carbon::parse($key)->format('d M'),
                'year' => Carbon::parse($key)->format('Y'),
                'logs' => $time_lines,
            ]);
        }
        return $order_time_line;
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

    /**
     * @param $procurements
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function searchByTitle($procurements, Request $request)
    {
        return collect($procurements)->filter(function ($procurement) use ($request) {
            return str_contains(strtoupper($procurement['title']), strtoupper($request->search));
        });
    }
}
