<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\PartnerBasicInformation;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use ReflectionException;
use Sheba\Loan\Completion;
use Sheba\ModificationFields;

class BusinessInfo implements Arrayable {
    use ModificationFields;
    /**
     * @var Resource
     */
    private $resource;
    private $profile;
    /** @var LoanRequestDetails */
    private $loanDetails;
    /**
     * @var Partner
     */
    private $partner;
    private $basic_information;
    private $business_additional_information;
    private $sales_information;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null) {

        $this->loanDetails = $request;
        if ($partner) {
            $this->partner                         = $partner;
            $this->basic_information               = $partner->basicInformations;
            $this->business_additional_information = $partner->business_additional_information;
            $this->sales_information               = $partner->sales_information;
        }
        if ($resource) {
            $this->resource = $resource;
            $this->profile  = $resource->profile;
        }
    }

    public static function getValidator() {
        return [
            'business_type'      => 'string',
            'location'           => 'required|string',
            'establishment_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            'full_time_employee' => 'numeric'
        ];
    }

    /**
     * @param Request $request
     * @throws ReflectionException
     */
    public function update(Request $request) {
        $partner_data       = [
            'business_type'                   => $request->business_type,
            'smanager_business_type'          => $request->smanager_business_type,
            'ownership_type'                  => $request->ownership_type,
            'stock_price'                     => (double)$request->stock_price,
            'address'                         => $request->location,
            'full_time_employee'              => $request->full_time_employee,
            'part_time_employee'              => $request->part_time_employee,
            'sales_information'               => (new SalesInfo($request->last_six_month_sales_information))->toString(),
            'business_additional_information' => (new BusinessAdditionalInfo($request->business_additional_information))->toString(),
            'yearly_income'                   => $request->yearly_income
        ];
        $partner_basic_data = [
            'establishment_year'       => $request->establishment_year,
            'tin_no'                   => $request->tin_no,
            'trade_license'            => $request->trade_license,
            'trade_license_issue_date' => $request->trade_license_issue_date,
            'business_category'        => $request->business_category,
            'sector'                   => $request->sector,
        ];
        $this->profile->update($this->withUpdateModificationField(['tin_no' => $request->tin_no]));
        $this->partner->update($this->withBothModificationFields($partner_data));
        if ($this->basic_information) {
            $this->basic_information->update($this->withBothModificationFields($partner_basic_data));
        } else {
            $partner_basic_data['partner_id'] = $this->partner->id;
            $this->basic_information          = new PartnerBasicInformation($partner_basic_data);
            $this->basic_information->save();
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function completion() {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->basic_information ? $this->basic_information->updated_at : null
        ], [
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
            'trade_license',
            'trade_license_issue_date',
        ]))->get();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray() {
        return $this->loanDetails ? $this->dataFromLoanRequest() + self::staticsData() : $this->dataFromProfile();
    }

    /**
     * @throws ReflectionException
     */
    private function dataFromLoanRequest() {
        $data = $this->loanDetails->getData();
        if (isset($data['business'])) {

            $data = $data['business'];
        } elseif (($data = $data[0]) && isset($data['business_info'])) {
            $data = $data['business_info'];
        } else {
            $data = [];
        }
        $keys   = self::getKeys();
        $output = [];
        foreach ($keys as $key) {
            if ($key == 'business_additional_information') {
                $set          = array_key_exists($key, $data) ? $data[$key] : null;
                $output[$key] = (new BusinessAdditionalInfo($set))->toArray();
            } elseif ($key == 'last_six_month_sales_information') {
                $set          = array_key_exists($key, $data) ? $data[$key] : null;
                $output[$key] = (new SalesInfo($set))->toArray();
            } else {
                $output[$key] = array_key_exists($key, $data) ? $data[$key] : null;
            }
        }
        $output['online_order'] = $this->getTotalOnlineOrderServed();
        return $output;
    }

    public static function getKeys() {
        return [
            'business_name',
            'business_type',
            'smanager_business_type',
            'ownership_type',
            'stock_price',
            'location',
            'establishment_year',
            'tin_no',
            'trade_license',
            'trade_license_issue_date',
            'yearly_income',
            'tin_certificate',
            'full_time_employee',
            'part_time_employee',
            'business_additional_information',
            'last_six_month_sales_information',
            'annual_cost',
            'fixed_asset',
            'security_check',
            'business_category',
            'sector',
            'industry_and_business_nature',
            'date_of_establishment',
            'strategic_partner',
            'short_name'
        ];
    }

    private function getTotalOnlineOrderServed() {
        return $this->partner->jobs()->where('status', 'Served')->count();
    }

    public static function staticsData() {
        return [
            'business_types'          => constants('PARTNER_BUSINESS_TYPES'),
            'smanager_business_types' => constants('PARTNER_SMANAGER_BUSINESS_TYPE'),
            'ownership_types'         => constants('PARTNER_OWNER_TYPES'),
            'business_categories'     => constants('PARTNER_BUSINESS_CATEGORIES'),
            'sectors'                 => constants('PARTNER_BUSINESS_SECTORS')
        ];
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function dataFromProfile() {

        return [
                   'business_name'                    => $this->partner->name,
                   'business_type'                    => $this->partner->business_type,
                   'smanager_business_type'           => $this->partner->smanager_business_type,
                   'ownership_type'                   => $this->partner->ownership_type,
                   'stock_price'                      => (double)$this->partner->stock_price,
                   'location'                         => $this->partner->address,
                   'establishment_year'               => $this->basic_information->establishment_year,
                   'tin_no'                           => $this->profile->tin_no,
                   'tin_certificate'                  => $this->profile->tin_certificate,
                   'trade_license'                    => $this->basic_information->trade_license,
                   'trade_license_issue_date'         => $this->basic_information->trade_license_issue_date,
                   'business_category'                => $this->basic_information->business_category,
                   'sector'                           => $this->basic_information->sector,
                   'yearly_income'                    => $this->partner->yearly_income,
                   'full_time_employee'               => (int)$this->partner->full_time_employee ?: null,
                   'part_time_employee'               => (int)$this->partner->part_time_employee ?: null,
                   'business_additional_information'  => (new BusinessAdditionalInfo($this->business_additional_information))->toArray(),
                   'last_six_month_sales_information' => (new SalesInfo($this->sales_information))->toArray()
               ] + self::staticsData();
    }
}
