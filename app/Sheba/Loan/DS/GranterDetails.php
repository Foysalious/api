<?php

namespace Sheba\Loan\DS;

use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
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
        if(isset($granter)){
            $pro_pic_link = $granter->pro_pic;
            if($photo = $request->file('pro_pic')) {
                if (basename($granter->pro_pic) != 'default.jpg') {
                    $filename = $granter->pro_pic;
                    $this->deleteOldImage($filename);
                }
                $pro_pic_link = (new FileRepository())->uploadToCDN($this->makePicName($granter, $photo), $photo, 'images/profiles/');

            }
            $granter->update($this->withCreateModificationField([
                'nid_no' => $request->grantor_nid_number,
                'pro_pic'=> $pro_pic_link
            ]));
        }
        $this->profile->update($this->withBothModificationFields([
            'grantor_id'       => $granter->id,
            'grantor_relation' => $request->grantor_relation
        ]));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function completion()
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
            'nid_image_back',
            'nid_image_front'
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

    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        (new FileRepository())->deleteFileFromCDN($old_image);
    }

    private function makePicName($profile, $photo)
    {
        return $filename = Carbon::now()->timestamp . '_image_' . $profile->id . '.' . $photo->extension();
    }
}
