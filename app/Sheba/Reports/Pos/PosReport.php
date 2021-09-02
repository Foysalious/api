<?php namespace Sheba\Reports\Pos;

use App\Models\Partner;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;
use Sheba\Reports\Pos\Sales\CustomerWise;
use Sheba\Reports\Pos\Sales\ProductWise;

abstract class PosReport
{
    /**
     * @var $request Request
     * @var $query Builder
     * @var $partner Partner
     *
     */
    protected $request, $orderBy, $range, $to, $from, $query, $order, $page, $limit, $data, $partner;
    /** @var ExcelHandler $excelHandler */
    private $excelHandler;
    /** @var PdfHandler $pdfHandler */
    private $pdfHandler;
    private $defaultOrderBy, $orderByAccessors;

    /** @var $client PosOrderServerClient */
    private $client;

    public function __construct()
    {
        $this->excelHandler = app(ExcelHandler::class);
        $this->pdfHandler = app(PdfHandler::class);
        $this->setDefaults();
        $this->client = app(PosOrderServerClient::class);
    }

    /**
     * @param $default_order_by
     * @return PosReport
     */
    protected function setDefaultOrderBy($default_order_by)
    {
        $this->defaultOrderBy = $default_order_by;
        return $this;
    }

    /**
     * @throws ValidationException
     */
    protected function validateRequest()
    {
        $rules = [
            'range' => 'required|in:today,week,month,year,yesterday,quarter,last_week,last_month,last_quarter,last_year,custom',
            'to' => 'required_with:name,custom|date_format:Y-m-d',
            'from' => 'required_with:name,custom|date_format:Y-m-d',
        ];
        if (isset($this->orderByAccessors)) {
            $rules['order_by'] = 'sometimes|in:' . $this->orderByAccessors;
        }
        $validator = Validator::make($this->request, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function prepareQueryParams()
    {
        $this->orderBy  = $this->hasInRequest('order_by') ?: $this->defaultOrderBy;
        $this->order    = $this->hasInRequest('order') ?: 'ASC';
        $this->range    = $this->hasInRequest('range') ?: null;
        $this->to       = $this->hasInRequest('to') ?: null;
        $this->from     = $this->hasInRequest('from') ?: null;
        $this->page     = $this->hasInRequest('page') ?: 1;
        $this->limit    = $this->hasInRequest('limit') ?: null;

        $this->setRange();
    }

    public function setRange()
    {
        if (!empty($this->range) && $this->range != 'custom') {
            $range = getRangeFormat($this->request, 'range');
            $this->from = $range[0];
            $this->to = $range[1];
        } else {
            $this->to = Carbon::parse($this->to)->endOfDay();
            $this->from = Carbon::parse($this->from)->startOfDay();
        }
    }

    /**
     * @param Request $request
     * @return $this
     * @throws ValidationException
     */
    protected function setRequest(Request $request)
    {
        $this->request = (array)$request->all();
        $this->validateRequest();
        $this->prepareQueryParams();

        return $this;
    }

    public function setOrderByAccessors($accessors)
    {
        $this->orderByAccessors = $accessors;
        return $this;
    }

    /**
     * @param $key
     * @return |null
     */
    private function hasInRequest($key)
    {
        if (isset($this->request[$key]) && !empty($this->request[$key])) return $this->request[$key];
        else return null;
    }

    private function setDefaults()
    {
        $this->defaultOrderBy = 'name';
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @return void
     * @throws NotAssociativeArray
     * @throws \Exception
     */
    public function downloadExcel($name = 'Sales Report')
    {
        return $this->excelHandler->setName($name)->createReport($this->data)->download();
    }

    /**
     * @param string $name
     * @param string $template
     * @return
     * @throws NotAssociativeArray
     */
    public function downloadPdf($name = 'Sales Report', $template = 'generic_template')
    {
        return $this->pdfHandler->setName($name)
            ->setViewFile($template)
            ->setData(['data' => $this->data, 'partner' => $this->partner, 'from' => $this->from, 'to' => $this->to])
            ->download();
    }

    abstract public function prepareData($paginate = true);

    abstract public function prepareQuery(Request $request, Partner $partner);

    public function getReportDataFromPosServer(string $report_for)
    {
        if ($report_for == CustomerWise::class){
            $uri = 'api/v1/partners/' . $this->partner->id . '/reports/customer-wise?from='. $this->from .'&to=' . $this->to;
            $report = $this->client->get($uri);
            return $report['data'];
        }
        elseif ($report_for == ProductWise::class){
            $uri = 'api/v1/partners/' . $this->partner->id . '/reports/product-wise?from='. $this->from .'&to=' . $this->to . '&order=' . $this->order .'&orderBy=' . $this->orderBy;
            $report = $this->client->get($uri);
            return $report['data'];
        }
    }
}
