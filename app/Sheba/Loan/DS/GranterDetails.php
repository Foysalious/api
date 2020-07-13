<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Sheba\Dal\PartnerBankLoan\LoanTypes;
use Sheba\Loan\Completion;
use Sheba\ModificationFields;

class GranterDetails implements Arrayable
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
    private $profile;
    /** @var Profile $nominee */
    private $nominee;
    /** @var Profile $granter */
    private $granter;
    /**
     * @var PartnerLoanRequest
     */
    private $loanDetails;

    public function __construct(Partner $partner = null, Resource $resource = null, LoanRequestDetails $request = null)
    {
        $this->partner     = $partner;
        $this->resource    = $resource;
        $this->loanDetails = $request;
        if ($this->resource) {
            $this->profile = $resource->profile;
            $this->setGranter();
        }
    }

    /**
     * @return GranterDetails
     */
    private function setGranter()
    {
        $this->granter = $this->profile->granter;
        return $this;
    }

    public static function getValidator()
    {
        return [
            'grantor_name'      => 'required|string',
            'grantor_mobile'    => 'required|string|mobile:bd',
            'grantor_relation'  => 'required|string',
            'grantor_nid_number'=> 'required|string|digits_between:10,17',
            'pro_pic'           => 'required|mimes:jpeg,png,jpg'
        ];
    }

    public static function getValidatorForTerm()
    {
        return [
            'grantor_name'       => 'required|string',
            'grantor_mobile'     => 'required|string|mobile:bd',
            'grantor_relation'   => 'required|string',
            'grantor_nid_number' => 'required|string|digits_between:10,17',
            'pro_pic'            => 'required|mimes:jpeg,png,jpg',
            'nid_image_front'    => 'required|mimes:jpeg,png,jpg',
            'nid_image_back'     => 'required|mimes:jpeg,png,jpg'
        ];
    }

    /**
     * @return mixed
     */
    public function getGranter()
    {
        return $this->granter;
    }

    /**
     * @param Request $request
     * @throws \ReflectionException
     */
    public function update(Request $request)
    {
        $granter = Profile::where('mobile', formatMobile($request->grantor_mobile))->first();
        if (empty($granter)) {
            $granter = (new GranterInfo([
                'name'   => $request->grantor_name,
                'mobile' => $request->grantor_mobile
            ]))->create($this->partner);
        }

        if(!isset($request->loan_type) || $request->loan_type == LoanTypes::TERM)
            $this->updateForTerm($request, $granter);

        $pro_pic_link = $granter->pro_pic;
        if($photo = $request->file('pro_pic')) {
            if (basename($granter->pro_pic) != 'default.jpg')
                $this->deleteOldImage($granter->pro_pic);

            $pro_pic_link = (new FileRepository())->uploadToCDN($this->makePicName($photo), $photo, 'images/profiles/');
        }
        $granter->update($this->withUpdateModificationField([
            'nid_no' => $request->grantor_nid_number,
            'pro_pic'=> $pro_pic_link
        ]));

        $this->profile->update($this->withUpdateModificationField([
            'grantor_id'       => $granter->id,
            'grantor_relation' => $request->grantor_relation
        ]));
    }

    /**
     * @param null $type
     * @return array
     * @throws \ReflectionException
     */
    public function completion($type = null)
    {
        $data = $this->toArray();
        return (new Completion($data, [
            $this->profile->updated_at,
            $this->granter ? $this->granter->updated_at : null
        ], [
            'address',
            'dob',
            'occupation',
            'net_worth',
            $type && $type == LoanTypes::MICRO ? 'nid_image_back' : null,
            $type && $type == LoanTypes::MICRO ? 'nid_image_front' : null
        ]))->get();
    }

    /**
     * @return array|void
     * @throws \ReflectionException
     */
    public function toArray()
    {
        return $this->loanDetails ? $this->getDataFromLoanRequest() : $this->getDataFromProfile();
    }

    private function updateForTerm(Request $request, $profile)
    {
        if($nid_image_front = $request->file('nid_image_front')) {
            if (isset($profile->nid_image_front))
                $this->deleteOldImage($profile->nid_image_front);

            $nid_image_front = (new FileRepository())->uploadToCDN($this->makePicName($nid_image_front, "_nid_image_front"), $nid_image_front, 'images/profiles/');
        }
        if($nid_image_back = $request->file('nid_image_back')) {
            if (isset($profile->nid_image_back))
                $this->deleteOldImage($profile->nid_image_back);

            $nid_image_back = (new FileRepository())->uploadToCDN($this->makePicName($nid_image_back, "_nid_image_back"), $nid_image_back, 'images/profiles/');
        }
        if(isset($nid_image_back) && isset($nid_image_front)){
            $profile->update(([
                'nid_image_back' => $nid_image_back,
                'nid_image_front' => $nid_image_front
            ]));
        }
    }

    private function getDataFromLoanRequest()
    {
        $data = $this->loanDetails->getData();
        if (isset($data['nominee_granter'])) {

            $data = $data['nominee_granter'];
        } elseif (($data = $data[0]) && isset($data['nominee_grantor_info'])) {
            $data = $data['nominee_grantor_info'];
        } else {
            $data = [];
        }
        return [
            'grantor' => array_merge((new GranterInfo((array_key_exists('grantor', $data) ? $data['grantor'] : null)))->toArray(), ['grantor_relation' => (array_key_exists('grantor', $data) ? $data['grantor']['grantor_relation'] : null)]),
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getDataFromProfile()
    {
        return [
            'grantor' => array_merge((new GranterInfo($this->granter ? $this->granter->toArray() : []))->toArray(), ['grantor_relation' => $this->profile->grantor_relation]),
        ];
    }

    private function makePicName($photo, $image_for="_pro_pic")
    {
        return $filename = Carbon::now()->timestamp . $image_for. "." . $photo->extension();
    }

    private function deleteOldImage($filename)
    {
        $filename = substr($filename, strlen(config('sheba.s3_url')));
        (new FileRepository())->deleteFileFromCDN($filename);
    }
}
