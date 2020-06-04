<?php namespace App\Sheba\Loan\DS;


use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;

use Sheba\Loan\DS\LoanRequestDetails;
use Sheba\ModificationFields;

class DocumentsForAgents implements Arrayable
{
    use ModificationFields;
    /**
     * @var Partner
     */
    private $partner;
    /**
     * @var Resource
     */
    private $resource;
    /**
     * @var string
     */
    private $profile;
    private $basic_information;
    private $nominee;
    /** @var LoanRequestDetails */
    private $loanDetails;
    private $granter;
    private $bank_information;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {
        $this->loanDetails = $request;
        $this->partner     = $partner;
        $this->resource    = $resource;
        if ($this->partner) {
            $this->basic_information = $this->partner->basicInformations;
            $this->bank_information  = $this->partner->bankInformations;
        }
        if ($this->resource) {
            $this->profile = $resource->profile;
        }

    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return  $this->getDataFromLoanRequest();
    }

    private function getDataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['document'])) {
            $data = $data['document'];
        } else {
            $data = $data[0];
            $data = isset($data['documents']) ? $data['documents'] : [];
        }
        $output = [];
        foreach (self::getKeys() as $key) {
            if ($key == 'retailer_document') {
                if (array_key_exists($key, $data)) {

                    $output[$key] = [
                        'application'         => array_key_exists('picture', $data[$key]) ? $data[$key]['picture'] : null,
                        'charge_document' => array_key_exists('nid_front_image', $data[$key]) ? $data[$key]['nid_front_image'] : null,
                        'credit_proposal'  => array_key_exists('nid_back_image', $data[$key]) ? $data[$key]['nid_back_image'] : null,
                    ];
                } else {
                    $output[$key] = [
                        'application'         => null,
                        'charge_document' => null,
                        'credit_proposal'  => null
                    ];
                }
            }
        }
        return $output;
    }

    public static function getKeys()
    {
        return [
            'picture',
            'is_verified',
            'nid_image_front',
            'nid_image_back',
            'nominee_document',
            'grantor_document',
            'business_document',
            'extras',
            'dls_v2_documents'
        ];
    }


}
