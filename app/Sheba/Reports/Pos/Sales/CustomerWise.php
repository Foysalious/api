<?php namespace Sheba\Reports\Pos\Sales;

use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PosOrder;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\UserMigration\Modules;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;
use Sheba\Reports\Pos\PosReport;

class CustomerWise extends PosReport
{
    /**
     * @var $itemRepository PosOrderItemRepository
     */
    private $excelHandler, $pdfHandler;
    private  $orderByAccessors;

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
        if($this->partner->isMigrated(Modules::POS)){
            $customer_sales =  $this->getReportDataFromPosServer(self::class);
        } else {
            $this->query->get()->each(function (PosOrder $pos_order) use (&$customer_sales) {
                $customer_id = $pos_order->customer_id;
                $partner_id  = $pos_order->partner_id;
                $pos_order->calculate();
                $is_customer_already_exist = (array_key_exists($customer_id, $customer_sales));
                if (!$is_customer_already_exist) {
                    $posProfile = PartnerPosCustomer::byPartner($partner_id)->where('customer_id', $customer_id)->first();
                    $customer_sales[$customer_id] = [
                        'customer_id'   => $customer_id,
                        'customer_name' => $posProfile ? $posProfile->nick_name ? $posProfile->nick_name : $pos_order->customer->profile->name : $pos_order->customer->profile->name,
                        'order_count'   => 0,
                        'sales_amount'  => 0.00
                    ];
                }
                $customer_sales[$customer_id]['order_count']    =  $is_customer_already_exist ? $customer_sales[$customer_id]['order_count']+=1 : 1;
                $customer_sales[$customer_id]['sales_amount']   =  $is_customer_already_exist ? $customer_sales[$customer_id]['sales_amount'] + $pos_order->getNetBill() : $pos_order->getNetBill();
                $customer_sales[$customer_id]['sales_due']      =  $is_customer_already_exist ? $customer_sales[$customer_id]['sales_due'] + $pos_order->getDue() : $pos_order->getDue();
            });
        }

        $customer_sales = collect($customer_sales);
        $this->setDefaultOrderBy('customer_name')->setOrderByAccessors('customer_name,order_count,sales_amount');
        $is_desc = $this->order == 'DESC';
        $customer_sales = $customer_sales->sortBy($this->orderBy, SORT_REGULAR, $is_desc);
        $total = $customer_sales->count();
        $total_order_count = $customer_sales->sum('order_count');
        $total_sales_count = $customer_sales->sum('sales_amount');

        $customer_sales = $customer_sales->values();
        $data = $paginate ? new Paginator($customer_sales, $this->limit) : $customer_sales;
        if ($paginate) {
            $this->data['data'] = $data->items();
            $this->data['total_order_count'] = $total_order_count;
            $this->data['total_sales_count'] = $total_sales_count;
            $this->data['total'] = $total;
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
        $this->setRequest($request);
        $this->partner = $partner;
        $this->query = $partner->posOrders()->with('customer.profile')->whereNotNull('customer_id')->whereBetween('created_at', [$this->from, $this->to]);

        return $this;
    }

    /**
     * @param string $name
     * @return void
     * @throws NotAssociativeArray
     */
    public function downloadExcel($name = 'Sales Report')
    {
        $data = $this->data->toArray();
        return $this->excelHandler->setName($name)->createReport($data)->download();
    }

    /**
     * @param string $name
     * @param string $template
     * @return
     * @throws NotAssociativeArray
     */
    public function downloadPdf($name = 'Sales Report', $template = 'generic_template')
    {
        $data = $this->data->toArray();
        return $this->pdfHandler->setName($name)
            ->setViewFile($template)
            ->setData(['data' => $data, 'partner' => $this->partner, 'from' => $this->from, 'to' => $this->to])
            ->download();
    }
}
