<?php namespace Sheba\NID\Validations;

use App\Models\Profile;
use Carbon\Carbon;
use Exception;

class NidValidation
{
    public static $RULES = ['nid' => 'required|digits_between:10,17|nid_number', 'full_name' => 'required', 'dob' => 'required|date_format:Y-m-d'];
    private       $vendorClass;
    private       $data;
    private       $profile;

    private $countVerify;

    /**
     * NidValidation constructor.
     *
     * @param string $vendor
     * @throws InvalidVendorException
     */
    public function __construct($vendor = 'porichoy')
    {
        try {
            $vendorClass = "Sheba\NID\Validations\\" . ucfirst(camel_case($vendor));
            $vendorClass = app($vendorClass);
            if (!($vendorClass instanceof NidValidator)) {
                throw new InvalidVendorException();
            }
            $this->vendorClass = $vendorClass;
            $this->countVerify = true;
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
     * @throws Exception
     */
    public function setData($data)
    {
        $this->data = $data;
        foreach ($this->data as $key => $val) {
            if (empty($val)) {
                throw new Exception("Invalid $key");
            }
        }
        return $this;
    }

    /**
     * @param      $nid
     * @param null $fullName
     * @param null $dob
     * @return array
     * @throws Exception
     */
    public function validate($nid, $fullName, $dob)
    {
        if (!$this->profile) {
            return ['status' => 0, 'message' => 'Profile not found'];
        }
        if (strlen($nid) == 13 && $dob) {
            $year = explode('-', $dob)[0];
            $nid  = ($year . $nid);
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
        if (!$this->countVerify) return true;
        $date  = $this->profile->last_nid_verification_request_date;
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
        $this->profile->nid_verification_request_count     = $count;
        $this->profile->last_nid_verification_request_date = $today;
        return $this->profile->save();

    }

    public function complete()
    {
        $today = Carbon::now();
        $data  = ['nid_verified' => 1, 'nid_verification_date' => $today];
        $data  = array_merge($data, $this->data);
        return $this->profile->update($data);
    }

    /**
     * @param mixed $countVerify
     * @return NidValidation
     */
    public function setCountVerify($countVerify)
    {
        $this->countVerify = $countVerify;
        return $this;
    }
}
