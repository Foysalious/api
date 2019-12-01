<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class BusinessInfo implements Arrayable
{
    use ModificationFields;
    /**
     * @var Resource
     */
    private $resource;
    private $profile;
    /**
     * @var PartnerLoanRequest
     */
    private $partnerLoanRequest;
    /**
     * @var Partner
     */
    private $partner;
    private $basic_information;
    private $bank_information;
    private $business_additional_information;
    private $sales_information;

    public function __construct(Partner $partner, Resource $resource, PartnerLoanRequest $request = null)
    {
        $this->resource                        = $resource;
        $this->profile                         = $resource->profile;
        $this->partnerLoanRequest              = $request;
        $this->partner                         = $partner;
        $this->basic_information               = $partner->basicInformations;
        $this->bank_information                = $partner->bankInformations;
        $this->business_additional_information = $partner->business_additional_information;
        $this->sales_information               = $partner->sales_information;
    }

    public static function getValidator()
    {
        return [
            'business_type'      => 'string',
            'location'           => 'required|string',
            'establishment_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            'full_time_employee' => 'numeric'
        ];
    }

    /**
     * @param Request $request
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        $partner_data       = [
            'business_type'                   => $request->business_type,
            'smanager_business_type'          => $request->smanager_business_type,
            'ownership_type'                  => $request->ownership_type,
            'stock_price'                     => (double)$request->stock_price,
            'address'                         => $request->location,
            'full_time_employee'              => $request->full_time_employee,
            'part_time_employee'              => $request->part_time_employee,
            'sales_information'               => (new SalesInfo($request->sales_information))->toString(),
            'business_additional_information' => (new BusinessAdditionalInfo($request->business_additional_information))->toString()
        ];
        $partner_basic_data = ['establishment_year' => $request->establishment_year];
        $this->partner->update($this->withBothModificationFields($partner_data));
        $this->basic_information->update($this->withBothModificationFields($partner_basic_data));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->partnerLoanRequest ? $this->dataFromLoanRequest() : $this->dataFromProfile();
    }

    private function dataFromLoanRequest()
    {
        return [];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function dataFromProfile()
    {

        return [
            'business_name'                    => $this->partner->name,
            'business_type'                    => $this->partner->business_type,
            'smanager_business_type'           => $this->partner->smanager_business_type,
            'ownership_type'                   => $this->partner->ownership_type,
            'stock_price'                      => (double)$this->partner->stock_price,
            'location'                         => $this->partner->address,
            'establishment_year'               => $this->basic_information->establishment_year,
            'full_time_employee'               => (int)$this->partner->full_time_employee ?: null,
            'part_time_employee'               => (int)$this->partner->part_time_employee ?: null,
            'business_additional_information'  => (new BusinessAdditionalInfo($this->business_additional_information))->toArray(),
            'last_six_month_sales_information' => (new SalesInfo($this->sales_information))->toArray(),
            'business_types'                   => constants('PARTNER_BUSINESS_TYPES'),
            'smanager_business_types'          => constants('PARTNER_SMANAGER_BUSINESS_TYPE'),
            'ownership_types'                  => constants('PARTNER_OWNER_TYPES')
        ];
    }
}
