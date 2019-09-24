<?php namespace Sheba\Reports\Pos\Sales;

use App\Models\Partner;
use App\Models\PosOrder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\PdfHandler;
use Sheba\Reports\Pos\PosReport;

class CustomerWise extends PosReport
{
    /**
     * @var $itemRepository PosOrderItemRepository
     */
    private $excelHandler, $pdfHandler;

    /**
     * ProductWise constructor.
     * @param ExcelHandler $excelHandler
     * @param PdfHandler $pdfHandler
     */
    public function __construct(ExcelHandler $excelHandler, PdfHandler $pdfHandler)
    {
        parent::__construct();
        $this->excelHandler = $excelHandler;
        $this->pdfHandler = $pdfHandler;
    }

    /**
     * @param bool $paginate
     * @return $this
     */
    public function prepareData($paginate = true)
    {
        $customer_sales = [];
        $this->query->get()->each(function (PosOrder $pos_order) use (&$customer_sales) {
            $customer_id = $pos_order->customer_id;
            $pos_order->calculate();
            $is_customer_already_exist = (array_key_exists($customer_id, $customer_sales));
            if (!$is_customer_already_exist) {
                $customer_sales[$customer_id] = [
                    'customer_name' => $pos_order->customer->profile->name,
                    'order_count'   => 0,
                    'sales_amount'  => 0.00
                ];
            }
            $customer_sales[$customer_id]['order_count']    =  $is_customer_already_exist ? $customer_sales[$customer_id]['order_count']++ : 1;
            $customer_sales[$customer_id]['sales_amount']   =  $is_customer_already_exist ? $customer_sales[$customer_id]['sales_amount'] + $pos_order->getNetBill() : $pos_order->getNetBill();
        });
        $customer_sales = array_values($customer_sales);
        $this->data = $paginate ? new Paginator($customer_sales, $this->limit) : $customer_sales;
        return $this;
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @return $this
     */
    public function prepareQuery(Request $request, Partner $partner)
    {
        $this->setDefaultOrderBy('pos_order_id');
        $this->setRequest($request);

        $this->query = $partner->posOrders()->with('customer.profile')
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$this->from, $this->to]);

        return $this;
    }
}
