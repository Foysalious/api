<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Sheba\Loan\Completion;
use Sheba\ModificationFields;

class Documents implements Arrayable
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
    private $type;
    private $version;

    /**
     * @param mixed $type
     * @return Documents
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $version
     * @return Documents
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {
        $this->loanDetails = $request;
        $this->partner     = $partner;
        $this->resource    = $resource;
        if ($this->partner) {
            $this->basic_information = $this->partner->basicInformations;
            $this->bank_information  = $this->partner->bankInformations->first();
        }
        if ($this->resource) {
            $this->profile = $resource->profile;
        }
        if ($this->profile) {
            $this->setNominee();
            $this->setGranter();
        }
    }

    /**
     * @return Documents
     */
    public function setNominee()
    {
        $this->nominee = $this->profile->nominee;
        return $this;
    }

    private function setGranter()
    {
        $this->granter = $this->profile->granter;
        return $this;
    }

    public function update() { }

    /**
     * @return array
     */
    public function completion($loan_type = null)
    {
        $data = $this->toArray($loan_type);
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->partner->updated_at,
            $this->basic_information ? $this->basic_information->updated_at : null,
            $this->bank_information ? $this->bank_information->updated_at : null
        ],['extra_images']))->get();
    }

    /**
     * @inheritDoc
     */
    public function toArray($loan_type = null)
    {
        return $this->loanDetails ? $this->getDataFromLoanRequest() : $this->getDataFromProfile($loan_type);
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
            if ($key == 'nominee_document' || $key == 'grantor_document') {
                if (array_key_exists($key, $data)) {
                    $output[$key] = [
                        'picture'         => array_key_exists('picture', $data[$key]) ? $data[$key]['picture'] : null,
                        'nid_front_image' => array_key_exists('nid_front_image', $data[$key]) ? $data[$key]['nid_front_image'] : null,
                        'nid_back_image'  => array_key_exists('nid_back_image', $data[$key]) ? $data[$key]['nid_back_image'] : null,
                    ];
                } else {
                    $output[$key] = [
                        'picture'         => null,
                        'nid_front_image' => null,
                        'nid_back_image'  => null
                    ];
                }
            } elseif ($key == 'business_document') {
                if (array_key_exists($key, $data)) {
                    $output[$key] = [
                        'tin_certificate'          => array_key_exists('tin_certificate', $data[$key]) ? $data[$key]['tin_certificate'] : null,
                        'trade_license_attachment' => array_key_exists('trade_license_attachment', $data[$key]) ? $data[$key]['trade_license_attachment'] : null,
                        'statement'                => array_key_exists('statement', $data[$key]) ? $data[$key]['statement'] : null,
                    ];
                } else {
                    $output[$key] = [
                        'tin_certificate'          => null,
                        'trade_license_attachment' => null,
                        'statement'                => null,
                    ];
                }
            } elseif ($key == 'extras') {
                $output[$key] = array_key_exists($key, $data) ? $data[$key] : null;
            } else {
                $output[$key] = array_key_exists($key, $data) ? $data[$key] : null;
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
            'retailer_document'
        ];
    }

    private function getDataFromProfile($loan_type = null)
    {
        $data = [
            'picture'           => $this->profile->pro_pic,
            'nid_image_front'   => $this->profile->nid_image_front,
            'nid_image_back'    => $this->profile->nid_image_back,
            'is_verified'       => $this->resource->is_verified,
            'business_document' => [
                'trade_license_attachment' => !empty($this->basic_information) ? $this->basic_information->trade_license_attachment : null,
                'tin_certificate'          => $this->profile->tin_certificate,
                'statement'                => !empty($this->bank_information) ? $this->bank_information->statement : null
            ],
        ];
        if(LoanTypes::MICRO === $loan_type) {
            $data['business_document'] = array_except($data['business_document'], ['tin_certificate', 'statement']);
            return $data;
        }
        $otherDoc = [
            'grantor_document'  => [
                'picture'         => !empty($this->granter) ? $this->granter->pro_pic : null,
                'nid_front_image' => !empty($this->granter) ? $this->granter->nid_image_front : null,
                'nid_back_image'  => !empty($this->granter) ? $this->granter->nid_image_back : null,
            ],
            'nominee_document'  => [
                'picture'         => !empty($this->nominee) ? $this->nominee->pro_pic : null,
                'nid_front_image' => !empty($this->nominee) ? $this->nominee->nid_image_front : null,
                'nid_back_image'  => !empty($this->nominee) ? $this->nominee->nid_image_back : null,
            ],
        ];
        if(LoanTypes::TERM === $loan_type) {
            $otherDoc = array_except($otherDoc, ['nominee_document', 'grantor_document']);
        }
        return array_merge($data,$otherDoc);
    }
}
