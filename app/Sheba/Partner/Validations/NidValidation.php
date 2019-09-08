<?php namespace Sheba\Partner\Validations;

use Carbon\Carbon;

class NidValidation
{
    public static $RULES = ['nid' => 'required|digits_between:10,17|nid_number', 'full_name' => 'required', 'dob' => 'required|date_format:Y-m-d'];
    private $vendorClass;

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
            dd($e);
            throw new InvalidVendorException();
        }
    }

    /**
     * @param $nid
     * @param null $fullName
     * @param null $dob
     * @return array
     */
    public function validate($nid, $fullName, $dob)
    {
        if (strlen($nid) == 13 && $dob) {
            $year = explode('-', $dob)[0];
            $nid .= ($year . $nid);
        }
        $dob = Carbon::parse($dob)->format('Y-m-d');
        return $this->vendorClass->check($nid, $fullName, $dob)->toArray();
    }
}
