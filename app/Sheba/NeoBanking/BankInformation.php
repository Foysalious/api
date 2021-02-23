<?php


namespace Sheba\NeoBanking;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\JsonBreakerTrait;

class BankInformation implements Arrayable
{
    use JsonBreakerTrait;

    protected $nid_selfie;
    protected $institution;
    protected $personal;
    protected $nominee;
    protected $documents;

    /**
     * @return mixed
     */
    public function getNidSelfie()
    {
        return $this->nid_selfie;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @return mixed
     */
    public function getPersonal()
    {
        return $this->personal;
    }

    /**
     * @return mixed
     */
    public function getNominee()
    {
        return $this->nominee;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

}
