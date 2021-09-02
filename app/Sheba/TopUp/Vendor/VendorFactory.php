<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpVendor;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use ReflectionClass;

class VendorFactory
{
    const MOCK = 1;
    const ROBI = 2;
    const AIRTEL = 3;
    const GP = 4;
    const BANGLALINK = 5;
    const TELETALK = 6;
    const SKITTO = 7;

    private $classes = [
        Mock::class,
        Robi::class,
        Airtel::class,
        Gp::class,
        Banglalink::class,
        Teletalk::class,
        Skitto::class,
    ];

    /**
     * @param $id
     * @return Vendor
     * @throws Exception
     */
    public function getById($id)
    {
        if (!in_array($id, $this->getConstants())) {
            throw new Exception('Invalid Vendor');
        }
        return app($this->classes[$id - 1])->setModel($this->getModel($id));
    }

    /**
     * @param $name
     * @return Vendor
     * @throws Exception
     */
    public function getByName($name)
    {
        $name = strtoupper($name);
        if (!in_array($name, array_keys($this->getConstants()))) {
            throw new Exception('Invalid Vendor');
        }
        $id = $this->getConstants()[$name];
        return app($this->classes[$id - 1])->setModel($this->getModel($id));
    }

    /**
     * @param $name
     * @return Vendor
     * @throws Exception
     */
    public function getIdByName($name)
    {
        $name = strtoupper($name);
        if (!in_array($name, array_keys($this->getConstants()))) {
            throw new Exception('Invalid Vendor');
        }
        return $this->getConstants()[$name];
    }

    /**
     * @param $mobile
     * @return Vendor
     * @throws Exception
     */
    public function getByMobile($mobile)
    {
        return $this->getById(1);
    }

    public function getModel($id)
    {
        return TopUpVendor::find($id);
    }

    /**
     * @return array
     */
    public static function sslVendorsId()
    {
        return [self::GP, self::TELETALK];
    }

    /**
     * @return Builder
     */
    public static function sslVendors()
    {
        return TopUpVendor::whereIn('id', self::sslVendorsId());
    }

    private function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
