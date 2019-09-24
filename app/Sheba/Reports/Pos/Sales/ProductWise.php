<?php namespace Sheba\Reports\Pos\Sales;

use App\Models\Partner;
use Illuminate\Http\Request;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
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
     * @param ExcelHandler $excelHandler
     * @param PdfHandler $pdfHandler
     */
    public function __construct(PosOrderItemRepository $posOrderItemRepository, ExcelHandler $excelHandler, PdfHandler $pdfHandler)
    {
        parent::__construct();
        $this->itemRepository = $posOrderItemRepository;
        $this->excelHandler = $excelHandler;
        $this->pdfHandler = $pdfHandler;
    }

    /**
     * @param bool $paginate
     * @return $this
     */
    public function prepareData($paginate = true)
    {
        $this->data = $paginate ? $this->query->paginate($this->limit) : $this->query->get();
        return $this;
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @return $this
     */
    public function prepareQuery(Request $request, Partner $partner)
    {
        $this->setDefaultOrderBy('service_name');
        $this->partner = $partner;
        $this->setRequest($request);
        $orders = $partner->posOrders()->select('id')->get()->pluck('id')->toArray();
        $this->query = $this->itemRepository
            ->getModel()
            ->whereIn('pos_order_id', $orders)
            ->selectRaw("service_id,service_name,CAST(SUM(quantity) as UNSIGNED) total_quantity ,CAST((SUM((quantity * unit_price))) as DECIMAL(10,2))  total_price,CAST(((SUM(unit_price)/SUM(quantity))) as DECIMAL(10,2)) avg_price,CAST((MAX(unit_price)) as DECIMAL(10,2)) as max_unit_price")
            ->whereBetween('created_at', [$this->from, $this->to])
            ->groupBy(['service_id', 'service_name'])
            ->orderBy($this->orderBy, $this->order);

        return $this;
    }
}
