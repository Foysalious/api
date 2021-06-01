<?php namespace App\Sheba\Webstore;

use App\Exceptions\DoNotReportException;
use App\Exceptions\HttpException;
use App\Models\Partner;
use Exception;

class PartnerService
{
    private $partner;

    public function setSubDomain($subDomain)
    {
        $this->partner = Partner::where('sub_domain',$subDomain)->first();
        if(!$this->partner)
          throw new HttpException('Partner does not exists', 404);
        return $this;
    }

    public function getDetails()
    {
        $partner = $this->partner;
        $data = collect($partner)->only([
            'id',
            'business_name',
            'sub_domain',
            'email',
            'logo',
            'address',
            'is_webstore_published'
        ]);
        $data->put('mobile', $partner->getContactNumber());
        return $data->toArray();
    }

}