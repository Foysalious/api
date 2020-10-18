<?php


namespace App\Sheba\NeoBanking\Banks;


class NidInformation
{

    /**
     * @var NidDetailsInfo $nidInfo
     */
    protected $nidInfo;

    /**
     * @param NidDetailsInfo $nidInfo
     * @return NidInformation
     */
    public function setNidInfo($nidInfo)
    {
        $this->nidInfo = $nidInfo;
        return $this;
    }

    public function toArray()
    {
        return $this->nidInfo;
    }

}