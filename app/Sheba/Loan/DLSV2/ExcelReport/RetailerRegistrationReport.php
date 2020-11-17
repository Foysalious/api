<?php


namespace App\Sheba\Loan\DLSV2\ExcelReport;


use App\Models\Partner;
use App\Models\PartnerBankLoan;
use App\Models\Resource;
use Illuminate\Http\Request;
use Sheba\Dal\Retailer\Retailer;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;

class RetailerRegistrationReport
{
    private $data;
    private $excelHandler;
    private $start_date;
    private $end_date;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setDates(Request $request)
    {
        $this->start_date = $request->start_date;
        $this->end_date = $request->end_date;
        return $this;
    }

    /**
     * @return bool
     * @throws NotAssociativeArray
     * @throws Exception
     */
    public function get()
    {
        $this->makeData();
        $this->excelHandler->setName("Retailer Registration Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {

        $robi_retailers = $this->generateData();

        foreach ($robi_retailers as $robi_retailer) {
            $this->data[] = [
                'sManager ID' => $robi_retailer->id,
                'sManager Name' => $robi_retailer->name,
                'sManager Phone Number' => $robi_retailer->getContactNumber(),
                'Registration Date' => $robi_retailer->created_at,
            ];
        }

    }


    /**
     * @return array
     */
    private function generateData()
    {
        $partners = Partner::whereBetween('created_at', [$this->start_date, $this->end_date])->get();

        $robi_retailers = [];
        foreach ($partners as $partner) {
            if ($this->is_retailer($partner)) {
                array_push($robi_retailers, $partner);
            }
        }

        return $robi_retailers;

    }

    /**
     * @param $partner
     * @return bool
     */
    private function is_retailer(Partner $partner)
    {
        if(!$partner->getContactResource())
            return false;
        return $partner->retailers()->where('strategic_partner_id', 2)->first() ?: false;
    }

}