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
use Sheba\Loan\CompletionStatics;
use Sheba\Loan\Statics\BusinessStatics;
use Sheba\ModificationFields;

class BusinessInfo implements Arrayable
{
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
    private $type;
    private $version;
    private $data;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {

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
    /**
     * @param Request $request
     * @throws ReflectionException
     */
    public function update(Request $request)
    {
        $partner_data       = [
            'business_type'           => $request->business_type,
            'smanager_business_type'  => $request->smanager_business_type,
            'ownership_type'          => $request->ownership_type,
            'stock_price'             => (double)$request->stock_price,
            'address'                 => $request->location,
            'full_time_employee'      => $request->full_time_employee,
            'part_time_employee'      => $request->part_time_employee,
            'sales_information'       => (new SalesInfo($request->last_six_month_sales_information))->toString(),
            BusinessStatics::INFO_KEY => (new BusinessAdditionalInfo($request->business_additional_information))->toString(),
            'yearly_income'           => $request->yearly_income
        ];
        $partner_basic_data = [
            'establishment_year'       => $request->establishment_year,
            'tin_no'                   => $request->tin_no,
            'trade_license'            => $request->trade_license,
            'trade_license_issue_date' => $request->trade_license_issue_date,
            'business_category'        => $request->business_category,
            'sector'                   => $request->sector,
            'registration_no'          => $request->registration_no,
            'registration_year'        => $request->registration_year
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
    public function completion()
    {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->basic_information ? $this->basic_information->updated_at : null
        ], CompletionStatics::business($this->version, $this->type)))->get();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray()
    {
        return $this->loanDetails ? $this->dataFromLoanRequest() + BusinessStatics::data() : $this->dataFromProfile();
    }

    /**
     * @throws ReflectionException
     */
    private function dataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['business'])) {

            $data = $data['business'];
        } elseif (($data = $data[0]) && isset($data['business_info'])) {
            $data = $data['business_info'];
        } else {
            $data = [];
        }
        $keys   = BusinessStatics::keys();
        $output = [];
        foreach ($keys as $key) {
            if ($key == BusinessStatics::INFO_KEY) {
                $set          = array_key_exists($key, $data) ? $data[$key] : null;
                $output[$key] = $this->version === 2 ? (new BusinessAdditionalInfo($set))->toVersionArray() : (new BusinessAdditionalInfo($set))->toArray();
            } elseif ($key == BusinessStatics::SALES_INFO_KEY) {
                $set          = array_key_exists($key, $data) ? $data[$key] : null;
                $output[$key] = (new SalesInfo($set))->toArray();
            } else {
                $output[$key] = array_key_exists($key, $data) ? $data[$key] : null;
            }
        }
        $output[BusinessStatics::ONLINE_ORDER_KEY] = $this->getTotalOnlineOrderServed();
        return $output;
    }
    private function getTotalOnlineOrderServed()
    {
        return $this->partner->jobs()->where('status', 'Served')->count();
    }


    /**
     * @return array
     * @throws ReflectionException
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
                   'tin_no'                           => $this->profile->tin_no,
                   'tin_certificate'                  => $this->profile->tin_certificate,
                   'trade_license'                    => $this->basic_information->trade_license,
                   'trade_license_issue_date'         => Carbon::parse($this->basic_information->trade_license_issue_date)->format('Y-m-d'),
                   'registration_no'                  => $this->basic_information->registration_no,
                   'registration_year'                => $this->basic_information->registration_year,
                   'business_category'                => $this->basic_information->business_category,
                   'sector'                           => $this->basic_information->sector,
                   'yearly_income'                    => $this->partner->yearly_income,
                   'full_time_employee'               => (int)$this->partner->full_time_employee ?: null,
                   'part_time_employee'               => (int)$this->partner->part_time_employee ?: null,
                   BusinessStatics::INFO_KEY          => $this->version === 2 ? (new BusinessAdditionalInfo($this->business_additional_information))->toVersionArray() : (new BusinessAdditionalInfo($this->business_additional_information))->toArray(),
                   BusinessStatics::SALES_INFO_KEY => (new SalesInfo($this->sales_information))->toArray()
               ] + BusinessStatics::data();
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $version
     * @return BusinessInfo
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param $url
     * @throws ReflectionException
     */
    public function updateProofOfBusinessPhoto($url)
    {

        if (empty($this->data)) $this->data = $this->toArray();
        $this->data[BusinessStatics::INFO_KEY][BusinessStatics::PHOTO_KEY] = $url;
        $this->partner->{BusinessStatics::INFO_KEY}               = (new BusinessAdditionalInfo($this->data[BusinessStatics::INFO_KEY]))->toString();
        $this->partner->save();
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function hasProofOfBusinessPhoto()
    {
        $this->data = $this->toArray();
        return isset($this->data[BusinessStatics::INFO_KEY][BusinessStatics::PHOTO_KEY]) && !empty($data[BusinessStatics::INFO_KEY][BusinessStatics::PHOTO_KEY]);
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function getProofOfBusinessPhoto()
    {
        if (empty($this->data)) $this->data = $this->toArray();
        return $this->data[BusinessStatics::INFO_KEY][BusinessStatics::PHOTO_KEY];
    }
}
