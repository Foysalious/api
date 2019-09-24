<?php namespace Sheba\Reports\Pos;

use App\Models\Partner;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\PdfHandler;

abstract class PosReport
{
    /**
     * @var $request Request
     * @var $query Builder
     * @var $partner Partner
     *
     */
    protected $request, $orderBy, $range, $to, $from, $query, $order, $page, $limit, $data, $partner;
    private $excelHandler, $pdfHandler;
    private $defaultOrderBy;

    public function __construct()
    {
        $this->excelHandler = app(ExcelHandler::class);
        $this->pdfHandler = app(PdfHandler::class);
        $this->setDefaults();
    }

    /**
     * @param mixed $defaultOrderBy
     * @return PosReport
     */
    protected function setDefaultOrderBy($defaultOrderBy)
    {
        $this->defaultOrderBy = $defaultOrderBy;
        return $this;
    }

    /**
     * @throws ValidationException
     */
    protected function validateRequest()
    {
        $validator = Validator::make($this->request, [
            'range' => 'required',
            'to' => 'required_with:name,custom',
            'from' => 'required_with:name,custom'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function prepareQueryParams()
    {
        $this->orderBy = $this->hasInRequest('order_by') ?: $this->defaultOrderBy;
        $this->order = $this->hasInRequest('order') ?: 'ASC';
        $this->range = $this->hasInRequest('range') ?: null;
        $this->to = $this->hasInRequest('to') ?: null;
        $this->from = $this->hasInRequest('from') ?: null;
        $this->page = $this->hasInRequest('page') ?: 1;
        $this->limit = $this->hasInRequest('limit') ?: null;

        $this->setRange();
    }

    public function setRange()
    {
        if (!empty($this->range) && $this->range != 'custom') {
            $range = getRangeFormat($this->request, 'range');
            $this->from = $range[0];
            $this->to = $range[1];
        }
    }

    protected function setRequest(Request $request)
    {
        $this->request = (array)$request->all();
        $this->validateRequest();
        $this->prepareQueryParams();

        return $this;
    }

    private function hasInRequest($key)
    {
        if (isset($this->request[$key]) && !empty($this->request[$key])) return $this->request[$key];
        else return null;
    }

    private function setDefaults()
    {
        $this->defaultOrderBy = 'name';
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @return
     */
    public function downloadExcel($name = 'Sales Report')
    {
        return $this->excelHandler->setName($name)->createReport($this->data->toArray())->download();
    }

    /**
     * @param string $name
     * @param string $template
     * @return
     */
    public function downloadPdf($name = 'Sales Report', $template = 'generic_template')
    {
        return $this->pdfHandler->setName($name)->setViewFile($template)->setData(['data' => $this->data->toArray(), 'partner' => $this->partner])->download();
    }

    abstract public function prepareData($paginate = true);

    abstract public function prepareQuery(Request $request, Partner $partner);
}
