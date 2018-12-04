<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 12/4/2018
 * Time: 12:40 PM
 */

namespace App\Sheba\TopUp\Commission;


class CommissionFactory
{
    const CUSTOMER = 1;
    const PARTNER = 2;
    const AFFILIATE = 3;

    private $classes = [
        Customer::class,
        Partner::class,
        Affiliate::class
    ];

    private $agent_classes = [
        \App\Models\Customer::class,
        \App\Models\Partner::class,
        \App\Models\Affiliate::class
    ];

    /**
     * @param @id
     * @throws \Exception
     */
    public function getById($id)
    {
        if(!in_array($id, $this->getConstants())) {
            throw new \Exception('Invalid Commission Model');
        }
        return app($this->classes[$id - 1]);
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function getByName($name)
    {
        if(!in_array($name, $this->agent_classes)) {
            throw new \Exception('Invalid Commission Model');
        }
        $id = array_search($name, $this->agent_classes);
        return app($this->classes[$id]);
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function getIdByName($name)
    {
        if(!in_array($name, $this->agent_classes)) {
            throw new \Exception('Invalid Commission Model');
        }
        return array_search($name, $this->agent_classes);
    }

    private function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}