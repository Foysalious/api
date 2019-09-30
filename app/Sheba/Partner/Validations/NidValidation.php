<?php namespace Sheba\Partner\Validations;

use App\Models\Profile;
use Carbon\Carbon;

class NidValidation
{
    public static $RULES = ['nid' => 'required|digits_between:10,17|nid_number', 'full_name' => 'required', 'dob' => 'required|date_format:Y-m-d'];
    private $vendorClass;
    private $data;
    private $profile;


    /**
     * NidValidation constructor.
     * @param string $vendor
     * @throws InvalidVendorException
     */
    public function __construct($vendor = 'porichoy')
    {
        try {
            $vendorClass = "Sheba\Partner\Validations\\" . ucfirst(camel_case($vendor));
            $vendorClass = app($vendorClass);
            if (!($vendorClass instanceof NidValidator)) {
                throw new InvalidVendorException();
            }
            $this->vendorClass = $vendorClass;
        } catch (\Throwable $e) {
            throw new InvalidVendorException();
        }
    }

    /**
     * @param mixed $profile
     * @return NidValidation
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data ?: [];
    }

    /**
     * @param mixed $data
     * @return NidValidation
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $nid
     * @param null $fullName
     * @param null $dob
     * @return array
     */
    public function validate($nid, $fullName, $dob)
    {
        if (!$this->profile) {
            return ['status' => 0, 'message' => 'Profile not found'];
        }
        if (strlen($nid) == 13 && $dob) {
            $year = explode('-', $dob)[0];
            $nid = ($year . $nid);
        }
        $dob = Carbon::parse($dob)->format('Y-m-d');
        $this->setData(['nid_no' => $nid, 'name' => $fullName, 'dob' => $dob]);
        if ($this->validateProfile()) {
            return $this->vendorClass->check($nid, $fullName, $dob)->toArray();
        } else {
            return ['status' => 0, 'message' => 'Request limit exceeded'];
        }

    }

    private function validateProfile()
    {
        $date = $this->profile->last_nid_verification_request_date;
        $count = $this->profile->nid_verification_request_count;
        if ($date && (Carbon::parse($date))->isToday()) {
            if ($count >= 3) {
                $this->updateNidApiCount();
                return false;
            } else {
                $this->updateNidApiCount();
                return true;
            }
        } else {
            $this->updateNidApiCount(true);
            return true;
        }
    }

    private function updateNidApiCount($notToday = false)
    {
        $today = Carbon::now();
        if ($notToday) {
            $count = 1;
        } else {
            $count = $this->profile->nid_verification_request_count;
            $count++;
        }
        $this->profile->nid_verification_request_count = $count;
        $this->profile->last_nid_verification_request_date = $today;
        return $this->profile->save();

    }

    public function complete()
    {
        $today = Carbon::now();
        $data = ['nid_verified' => 1, 'nid_verification_date' => $today];
        $data = array_merge($data, $this->data);
        return $this->profile->update($data);
    }
}
