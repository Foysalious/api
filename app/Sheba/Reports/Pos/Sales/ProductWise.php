<?php namespace Sheba\Reports\Pos\Sales;

use App\Models\Partner;
use App\Sheba\UserMigration\Modules;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\PdfHandler;
use Sheba\Reports\Pos\PosReport;

class ProductWise extends PosReport
{
    /** @var $itemRepository PosOrderItemRepository */
    private $itemRepository;
    /** @var ExcelHandler $excelHandler */
    private $excelHandler;
    /** @var PdfHandler $pdfHandler */
    private $pdfHandler;

    /**
     * ProductWise constructor.
     * @param PosOrderItemRepository $posOrderItemRepository
     */
    public function __construct(PosOrderItemRepository $posOrderItemRepository)
    {
        $this->itemRepository = $posOrderItemRepository;
        parent::__construct();
    }

    /**
     * @param bool $paginate
     * @return $this
     */
    public function prepareData($paginate = true)
    {
        if($this->partner->isMigrated(Modules::POS)){
            $full =  collect($this->getReportDataFromPosServer(self::class));
        } else {
            $full = $this->query->get();
        }

        $data = $paginate ? $full->paginate($this->limit) : $full->toArray();
        if ($paginate) {
            $this->data['data'] = $data->items();
            $this->data['total_price'] = $full->sum('total_price');
            $this->data['total_quantity'] = $full->sum('total_quantity');
            $this->data['total'] = $data->total();
            $this->data['has_more'] = $data->hasMorePages();
            $this->data['per_page'] = $data->perPage();
        } else {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @return $this
     * @throws ValidationException
     */
    public function prepareQuery(Request $request, Partner $partner)
    {
        $this->setDefaultOrderBy('service_name')
            ->setOrderByAccessors('service_name,total_price,total_quantity')
            ->setRequest($request);
        $this->partner = $partner;
        $orders = $partner->posOrders()->select('id')->get()->pluck('id')->toArray();
        $this->query = $this->itemRepository
            ->getModel()
            ->whereIn('pos_order_id', $orders)
            ->selectRaw("service_id,service_name,CAST(SUM(quantity) as UNSIGNED) total_quantity ,CAST((SUM((quantity * unit_price))) as DECIMAL(10,2))  total_price,CAST(((SUM((unit_price * quantity))/SUM(quantity))) as DECIMAL(10,2)) avg_price,CAST((MAX(unit_price)) as DECIMAL(10,2)) as max_unit_price")
            ->whereBetween('created_at', [$this->from, $this->to])
            ->groupBy(['service_id', 'service_name'])
            ->orderBy($this->orderBy, $this->order);

        return $this;
    }
}
